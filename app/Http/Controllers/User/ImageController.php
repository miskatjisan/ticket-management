<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\DownloadFile;
use App\Lib\FTPFileManager;
use App\Models\Category;
use App\Models\Color;
use App\Models\Image;
use App\Models\Like;
use App\Rules\FileTypeValidate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Image as ImageFacade;

class ImageController extends Controller
{
    public function all()
    {
        $pageTitle = "All Images";
        $images    = $this->imageData();
        return view($this->activeTemplate . 'user.image.list', compact('pageTitle', 'images'));
    }

    public function pending()
    {
        $pageTitle = "Pending Images";
        $images    = $this->imageData('pending');
        return view($this->activeTemplate . 'user.image.list', compact('pageTitle', 'images'));
    }

    public function rejected()
    {
        $pageTitle = "Rejected Images";
        $images    = $this->imageData('rejected');
        return view($this->activeTemplate . 'user.image.list', compact('pageTitle', 'images'));
    }

    public function approved()
    {
        $pageTitle = "Approved Images";
        $images    = $this->imageData('approved');
        return view($this->activeTemplate . 'user.image.list', compact('pageTitle', 'images'));
    }

    public function add()
    {
        $pageTitle  = "Upload Image";
        $categories = Category::active()->orderBy('name')->get();
        $colors     = Color::all();
        return view($this->activeTemplate . 'user.image.upload', compact('pageTitle', 'categories', 'colors'));
    }


    public function store(Request $request)
    {
        $user           = auth()->user();
        $general        = gs();
        $dailyUpload    = Image::where('user_id', $user->id)->whereDate('created_at', Carbon::now())->count();

        if ($general->upload_limit < $dailyUpload) {
            $notify[] = ['error', 'Daily upload limit has been over'];
            return back()->withNotify($notify);
        }

        $this->validation($request);

        $category = Category::active()->find($request->category);
        if (!$category) {
            $notify[] = ['error', 'Category not found'];
            return back()->withNotify($notify);
        }

        $tagCount =  count($request->tags);

        if ($tagCount > 10) {
            $notify[] = ['error', 'you can not use more than 10 tags'];
            return back()->withNotify($notify);
        }

        $image    = new Image();
        $response = $this->processImageData($image, $request);

        if (array_key_exists('error', $response)) {
            $notify[] = ['error', $response['error']];
        } else {
            $notify[] = ['success', $response['success']];
        }
        return back()->withNotify($notify);
    }


    public function edit($id)
    {
        $image      = Image::where('user_id', auth()->id())->findOrFail($id);
        $pageTitle  = 'Update image - ' . $image->title;
        $categories = Category::active()->orderBy('name')->get();
        $colors     = Color::all();
        return view($this->activeTemplate . 'user.image.upload', compact('pageTitle', 'categories', 'colors', 'image'));
    }

    public function updateImage(Request $request, $id)
    {
        $user           = auth()->user();
        $general        = gs();

        $image = Image::where('user_id', $user->id)->findOrFail($id);
        $this->validation($request, true);

        $category = Category::active()->find($request->category);
        if (!$category) {
            $notify[] = ['error', 'Category not found'];
            return back()->withNotify($notify);
        }

        if ($general->storage_type == 1) {
            if ($request->hasFile('photo')) {
                $photo      = getFilePath('stockImage') . '/' . $image->image_name;
                $photoThumb = getFilePath('stockImage') . '/' . $image->thumb;
                removeFile($photo);
                removeFile($photoThumb);
            }

            if ($request->hasFile('file')) {
                $file = getFilePath('stockFile') . '/' . $image->file;
                removeFile($file);
            }
        }

        $response = $this->processImageData($image, $request, true);

        if (array_key_exists('error', $response)) {
            $notify[] = ['error', $response['error']];
        } else {
            $notify[] = ['success', $response['success']];
        }
        return back()->withNotify($notify);
    }

    public function updateLike(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()]);
        }

        $user = auth()->user();
        $image = Image::where('id', $request->image)->first();

        if (!$image) {
            return response()->json(['error' => 'Image not found']);
        }

        $like = Like::where('image_id', $image->id)->where('user_id', $user->id)->first();

        if (!$like) {
            $like           = new Like();
            $like->user_id  = $user->id;
            $like->image_id = $image->id;
            $like->save();
            $image->total_like += 1;
        } else {
            $like->delete();
            $image->total_like -= 1;
        }

        $image->save();
        $userTotalLike = Image::where('user_id', $image->user_id)->sum('total_like');

        return response()->json(['status' => 'success', 'total_like' => $image->total_like, 'user_total_like' => $userTotalLike]);
    }

    public function download($id)
    {
        $image = Image::FindOrFail($id);
        $user = auth()->user()->load('downloads');
        if ($image->user_id == $user->id || $user->downloads->where('image_id', $image->id)->first()) {
            return DownloadFile::download($image);
        } else {
            $notify[] = ['error', 'Invalid Request'];
            return to_route('user.image.all')->withNotify($notify);
        }
    }

    public function changeActiveStatus($id)
    {
        $image = Image::where('user_id', auth()->id())->findOrFail($id);
        $image->is_active = $image->is_active ? Status::DISABLE : Status::ENABLE;
        $image->save();

        $notification = 'Image deactivated successfully';
        if ($image->is_active) {
            $notification = 'Image activated successfully';
        }
        $notify[] = ['success', $notification];
        return back()->withNotify($notify);
    }

    protected function imageData($scope = null)
    {
        $user   = auth()->user();
        $images = Image::where('user_id', $user->id);

        if ($scope) {
            $images = $images->$scope();
        }

        return $images->with('category')->orderBy('id', 'desc')->paginate(getPaginate(21));
    }

    protected function processImageData($image, $request, $isUpdate = false)
    {
        $user    = auth()->user();
        $general = gs();

        $directory     = date("Y") . "/" . date("m") . "/" . date("d");
        $imageLocation = getFilePath('stockImage') . '/' . $directory;
        $fileLocation  = getFilePath('stockFile') . '/' . $directory;

        if ($request->hasFile('photo')) {
            try {
                $filename  = uniqid() . time() . '.' . $request->photo->getClientOriginalExtension();
                $photo     = ImageFacade::make($request->photo);
                $watermark = ImageFacade::make('assets/images/watermark.png')->opacity(45)->rotate(45)->greyscale()->fit($photo->width(), $photo->height());
                $photo->insert($watermark, 'center');

                $thumb = ImageFacade::make($request->photo);
                $thumb->resize(600, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                $image->image_width = $thumb->width();
                $image->image_height = $thumb->height();

                if ($general->storage_type == 1) {
                    //configure image and thumb
                    if (!file_exists($imageLocation)) {
                        mkdir($imageLocation, 0755, true);
                    }
                    $photo->save($imageLocation . '/' . $filename);
                    $thumb->save($imageLocation . '/thumb_' . $filename);
                } else {
                    $ftpFileManager       = new FTPFileManager();
                    $ftpFileManager->path = 'images/' . $directory;
                    $ftpFileManager->old  = @$image->image_name;
                    $ftpFileManager->uploadImage($photo, $filename);
                    $ftpFileManager->uploadImage($thumb, $filename, true);
                }

                $image->image_name = $directory . '/' . $filename;
                $image->thumb = $directory . '/thumb_' . $filename;
            } catch (\Exception $exp) {
                return ['error' =>  $exp->getMessage()];
            }
        }

        if ($request->hasFile('file')) {
            if ($general->storage_type == 1) {
                try {
                    $fileName    = fileUploader($request->file, $fileLocation);
                    $image->file = $directory . '/' . $fileName;
                } catch (\Exception $exp) {
                    return ['error' => $exp->getMessage()];
                }
            } else {
                try {
                    $fileName    = ftpFileUploader($request->file, 'files/' . $directory, null, @$image->file);
                    $image->file = $directory . '/' . $fileName;
                } catch (\Exception $exp) {
                    return ['error' =>  $exp->getMessage()];
                }
            }
        }

        $image->user_id     = $user->id;
        $image->category_id = $request->category;
        $image->title       = $request->title;
        $image->description = $request->description;

        if (!$isUpdate) {
            $image->date        = now();
            $image->track_id    =  getTrx();
            $image->status      = $general->auto_approval ? 1 : 0;
        }

        $image->tags        = $request->tags;
        $image->extensions  = $request->extensions;
        $image->colors      = $request->colors;
        $image->resolution  = $request->resolution;
        $image->is_free     = $request->is_free;
        $image->attribution = $request->is_free ? 1 : 0;
        $image->price = $request->is_free ? 0 : $request->price;
        $image->save();

        $notification = 'Image uploaded successfully';
        if ($isUpdate) {
            $notification = 'Image updated successfully';
        }

        return ['success' => $notification];
    }

    protected function validation($request, $isUpdate = false)
    {

        $fileExtensions = getFileExt('file_extensions');
        $colors         = Color::pluck('color_code')->implode(',');

        $photoValidation = 'required';
        $fileValidation  = 'required';

        if ($isUpdate) {
            $photoValidation = 'nullable';
            $fileValidation  = 'nullable';
        }

        $request->validate([
            'category'       => 'required|integer|gt:0',
            'photo'          => [$photoValidation, new FileTypeValidate(['jpg', 'png', 'jpeg'])],
            'file'           => [$fileValidation, new FileTypeValidate(['zip', '7z', 'rar', 'tar', 'wim'])],
            'title'          => 'required|max:40',
            'resolution'     => 'required|string|max:40',
            'description'    => 'required|string',
            'tags'           => 'required|array',
            'tags.*'         => 'required|string',
            'colors'         => 'required|array',
            'colors.*'       => 'required|in:' . $colors,
            'extensions'     => 'required|array',
            'extensions.*'   => 'required|string|in:' . implode(',', $fileExtensions),
            'is_free'        => 'required|in:0,1',
            'price'          => 'nullable|required_if:is_free,0|numeric|gte:0'
        ], [
            'price.required_if' => 'Price field is required if the image is premium'
        ]);
    }
}
