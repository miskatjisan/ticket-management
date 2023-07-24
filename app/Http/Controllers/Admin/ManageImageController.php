<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Models\Image;
use App\Models\Category;
use App\Models\Download;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Lib\DownloadFile;
use App\Models\Color;
use App\Models\Reason;

class ManageImageController extends Controller
{
    public function all()
    {
        $pageTitle = 'All Images';
        $images    = $this->imageData();
        return view('admin.images.list', compact('pageTitle', 'images'));
    }

    public function pending()
    {
        $pageTitle = 'Pending Images';
        $images    = $this->imageData('pending');
        return view('admin.images.list', compact('pageTitle', 'images'));
    }

    public function rejected()
    {
        $pageTitle = 'Rejected Images';
        $images    = $this->imageData('rejected');
        return view('admin.images.list', compact('pageTitle', 'images'));
    }

    public function approved()
    {
        $pageTitle = 'Approved Images';
        $images    = $this->imageData('approved');
        return view('admin.images.list', compact('pageTitle', 'images'));
    }

    public function updateFeature($id)
    {
        $image = Image::findOrFail($id);

        $notification = 'Image un-featured successfully';
        $image->is_featured = $image->is_featured ? Status::DISABLE : Status::ENABLE;
        $image->save();

        if ($image->is_featured) {
            $notification = 'Image featured successfully';
        }

        $notify[] = ['success', $notification];
        return back()->withNotify($notify);
    }

    public function details($id)
    {
        $image      = Image::with('category', 'user')->findOrFail($id);
        $pageTitle  = 'Image Details - ' . $image->title;
        $categories = Category::active()->orderBy('name', 'asc')->get();
        $colors      = Color::orderBy('name', 'desc')->get();
        $extensions = getFileExt('file_extensions');
        $reasons = Reason::all();
        return view('admin.images.detail', compact('pageTitle', 'image', 'categories', 'colors', 'extensions', 'reasons'));
    }

    public function downloadLog($id)
    {
        $image     = Image::findOrFail($id);
        $logs      = Download::where('image_id', $image->id)->with('user', 'contributor', 'image')->paginate(getPaginate());
        $pageTitle = 'Download logs - ' . $image->title;
        return view('admin.images.download_log', compact('pageTitle', 'logs'));
    }

    public function update(Request $request, $id)
    {
        if (!$request->is_free) {
            $priceValidation = 'required|numeric|gt:0';
        } else {
            $priceValidation = 'nullable';
        }

        $extensions = getFileExt('file_extensions');
        $colors = Color::select('color_code')->pluck('color_code')->toArray() ?? [];

        $request->validate([
            'category'      => 'required|integer|gt:0',
            'title'         => 'required|string|max:40',
            'resolution'    => 'required|string|max:40',
            'tags'          => 'required|array',
            'tags.*'        => 'required|string',
            'extensions'    => 'required|array',
            'extensions.*'  => 'required|in:' . implode(',', $extensions),
            'colors'        => 'required|array',
            'colors.*'      => 'required|in:' . implode(',', $colors),
            'status'        => 'nullable|in:0,1,3',
            'is_free'       => 'nullable',
            'price'         => $priceValidation,
            'reason'        => 'required_if:status,3'
        ], [
            'extensions.*.in' => 'Extensions are invalid',
            'colors.*.in' => 'Colors are invalid'
        ]);

        $category = Category::active()->find($request->category);
        if (!$category) {
            $notify[] = ['error', 'Category not found'];
            return back()->withNotify($notify);
        }

        $image = Image::with('category')->findOrFail($id);

        $image->category_id   = $request->category;
        $image->title         = $request->title;
        $image->resolution    = $request->resolution;
        $image->tags          = $request->tags;
        $image->extensions    = $request->extensions;
        $image->colors        = $request->colors;
        $image->attribution   = $request->attribution ? Status::ENABLE : Status::DISABLE;
        $image->is_free       = $request->is_free ? Status::ENABLE : Status::DISABLE;
        $image->is_active     = $request->is_active ? Status::ENABLE : Status::DISABLE;
        $image->price         = $request->price;
        $image->status        = $request->status;
        $image->admin_id      = auth('admin')->id();
        $image->reviewer_id = 0;
        if ($image->status == 3) {
            $image->reason = $request->reason;
        }

        $image->save();

        if ($image->status == 3) {
            notify($image->user, 'IMAGE_REJECT', [
                'title' => $image->title,
                'category' => $image->category->name,
                'reason' =>  $image->reason
            ]);
        } elseif ($image->status == 1) {
            notify($image->user, 'IMAGE_APPROVED', [
                'title' => $image->title,
                'category' => $image->category->name
            ]);
        }

        $notify[] = ['success', 'Image updated successfully'];
        return back()->withNotify($notify);
    }

    public function downloadFile($id)
    {
        $image = Image::findOrFail($id);
        return DownloadFile::download($image);
    }

    protected function imageData($scope = null)
    {
        if ($scope) {
            $images = Image::$scope();
        } else {
            $images = Image::query();
        }
        return  $images->searchAble(['title', 'category:name', 'user:username,firstname,lastname', 'collections:title', 'admin:username,name', 'reviewer:username,name'])->orderBy('id', 'desc')->with('category', 'user')->paginate(getPaginate());
    }
}
