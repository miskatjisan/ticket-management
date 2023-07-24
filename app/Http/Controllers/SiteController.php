<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\Models\Collection;
use App\Models\Download;
use App\Models\Follow;
use App\Models\Frontend;
use App\Models\Image;
use App\Models\Language;
use App\Models\Page;
use App\Models\Plan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class SiteController extends Controller
{
    private $memberRelation = ['images', 'downloads', 'publicCollections', 'privateCollections', 'followers', 'followings'];

    public function index()
    {
        $reference = @$_GET['ref'];
        if ($reference) {
            session()->put('reference', $reference);
        }
        $pageTitle = 'Home';
        $sections  = Page::where('tempname', $this->activeTemplate)->where('slug', '/')->first();
        $images = Image::active()->with('user', 'likes')->orderBy('id', 'DESC')->limit(28)->get();
        return view($this->activeTemplate . 'home', compact('pageTitle', 'sections', 'images'));
    }

    public function pages($slug)
    {
        $page      = Page::where('tempname', $this->activeTemplate)->where('slug', $slug)->firstOrFail();
        $pageTitle = $page->name;
        $sections  = $page->secs;
        return view($this->activeTemplate . 'pages', compact('pageTitle', 'sections'));
    }

    public function policyPages($slug, $id)
    {
        $policy = Frontend::where('id', $id)->where('data_keys', 'policy_pages.element')->firstOrFail();
        $pageTitle = $policy->data_values->title;
        return view($this->activeTemplate . 'policy', compact('policy', 'pageTitle'));
    }

    public function changeLanguage($lang = null)
    {
        $language = Language::where('code', $lang)->first();
        if (!$language) $lang = 'en';
        session()->put('lang', $lang);
        return back();
    }


    public function cookieAccept()
    {
        $general = gs();
        Cookie::queue('gdpr_cookie', $general->site_name, 43200);
    }

    public function cookiePolicy()
    {
        $pageTitle = 'Cookie Policy';
        $cookie = Frontend::where('data_keys', 'cookie.data')->first();
        return view($this->activeTemplate . 'cookie', compact('pageTitle', 'cookie'));
    }

    public function placeholderImage($size = null)
    {
        $imgWidth = explode('x', $size)[0];
        $imgHeight = explode('x', $size)[1];
        $text = $imgWidth . '×' . $imgHeight;
        $fontFile = realpath('assets/font/RobotoMono-Regular.ttf');
        $fontSize = round(($imgWidth - 50) / 8);
        if ($fontSize <= 9) {
            $fontSize = 9;
        }
        if ($imgHeight < 100 && $fontSize > 30) {
            $fontSize = 30;
        }
        $image     = imagecreatetruecolor($imgWidth, $imgHeight);
        $colorFill = imagecolorallocate($image, 100, 100, 100);
        $bgFill    = imagecolorallocate($image, 175, 175, 175);
        imagefill($image, 0, 0, $bgFill);
        $textBox = imagettfbbox($fontSize, 0, $fontFile, $text);
        $textWidth  = abs($textBox[4] - $textBox[0]);
        $textHeight = abs($textBox[5] - $textBox[1]);
        $textX      = ($imgWidth - $textWidth) / 2;
        $textY      = ($imgHeight + $textHeight) / 2;
        header('Content-Type: image/jpeg');
        imagettftext($image, $fontSize, 0, $textX, $textY, $colorFill, $fontFile, $text);
        imagejpeg($image);
        imagedestroy($image);
    }

    public function maintenance()
    {
        $pageTitle = 'Maintenance Mode';
        $general = gs();
        if ($general->maintenance_mode == Status::DISABLE) {
            return to_route('home');
        }
        $maintenance = Frontend::where('data_keys', 'maintenance.data')->first();
        return view($this->activeTemplate . 'maintenance', compact('pageTitle', 'maintenance'));
    }

    public function plans()
    {
        $pageTitle = "Plans";
        $plans     = Plan::active()->get();
        $activePlans = Plan::active()->get();
        return view($this->activeTemplate . 'plans', compact('pageTitle', 'plans', 'activePlans'));
    }

    public function txtDownload()
    {
        $general = gs();
        $filepath = 'assets/license/license.txt';
        $fileName =  $general->site_name . '_' . 'license.txt';
        $headers = [
            'Cache-Control' => 'no-store, no-cache'
        ];
        return response()->download($filepath, $fileName, $headers);
    }

    public function collections()
    {
        $pageTitle   = "Collections";
        $collections = Collection::public()->with('images', 'user')->whereHas('images')->orderBy('id', 'DESC')->paginate(getPaginate());
        return view($this->activeTemplate . 'collections', compact('pageTitle', 'collections'));
    }

    public function collectionDetail($slug, $id)
    {
        $collection = Collection::findOrFail($id);
        $pageTitle  = 'Collection - ' . $collection->title;
        $collectionImages = Image::active()->whereHas('collections', function ($query) use ($id) {
            $query->where('collection_id', $id);
        })->with('user', 'likes')->paginate(getPaginate());
        return view($this->activeTemplate . 'collection_details', compact('collectionImages', 'pageTitle', 'collection'));
    }

    public function members()
    {
        $pageTitle = "All Members";
        $user      = auth()->user();
        $members   = User::active()->withCount('images')->orderBy('images_count', 'DESC')->paginate(getPaginate());
        $heading   = 'Members';
        return view($this->activeTemplate . 'member.all', compact('pageTitle', 'members', 'heading'));
    }

    public function memberImages($username)
    {

        $member      = User::withCount($this->memberRelation)->where('username', $username)->firstOrFail();

        $pageTitle   = "Member Images";
        $seoContents = $this->memberSeoContent($member);
        $images      = Image::with('user', 'likes')->where('user_id', $member->id)->active()->orderBy('id', 'DESC')->paginate(getPaginate());

        return view($this->activeTemplate . 'member.images', compact('pageTitle', 'member', 'images', 'seoContents'));
    }

    public function memberCollections($username)
    {
        $user = auth()->user();
        $member      = User::withCount($this->memberRelation)->where('username', $username)->firstOrFail();
        $pageTitle   = "Member Collections";


        $collections = Collection::where('user_id', $member->id)->public()->with(['images' => function ($query) {
            $query->where('status', 1)->where('is_active', 1);
        }, 'user'])->paginate(getPaginate(16));
        if ($user && $user->id == $member->id) {
            $collections = Collection::where('user_id', $member->id)->with(['images' => function ($query) {
                $query->where('status', 1)->where('is_active', 1);
            }, 'user'])->paginate(getPaginate(16));
        }
        return view($this->activeTemplate . 'member.collections', compact('pageTitle', 'member', 'collections'));
    }

    public function memberFollowerFollowings($username)
    {
        $member = User::with([
            'followers' => function ($followers) {
                $followers->orderBy('id', 'desc')->limit(21);
            }, 'followings' => function ($followings) {
                $followings->orderBy('id', 'desc')->limit(21);
            },
            'followers.followerProfile',
            'followings.followingProfile'
        ])
            ->withCount($this->memberRelation)->where('username', $username)->firstOrFail();

        $pageTitle = "About " . $member->fullname;

        return view($this->activeTemplate . 'member.about', compact('pageTitle', 'member'));
    }

    public function imageDetail($slug, $id)
    {
        $image = Image::with('user')->findOrFail($id);
        $this->incrementTotalView($image);

        $pageTitle = $image->title;
        $imagePath = getFilePath('stockImage') . '/' . @$image->image_name;
        $seoContents = getSeoContents($image->tags, $image->title, $image->description, $imagePath);

        $user = auth()->user();
        $todayDownload   = 0;
        $monthlyDownload = 0;
        $alreadyDownloaded = false;
        if (!$image->is_free && $user) {
            $downloads = Download::where('image_id', $image->id)->where('user_id', @$user->id)->where('premium', 1);
            $todayDownload = (clone $downloads)->whereDate('created_at', Carbon::now())->count();
            $monthlyDownload = (clone $downloads)->whereMonth('created_at', Carbon::now()->month)->count();
            $alreadyDownloaded = Download::where('image_id', $image->id)->where('user_id', @$user->id)->exists();
        }

        $relatedImages = Image::active()->where('id', '!=', $image->id)->where('category_id', $image->category_id)->with('user', 'likes')->orderBy('id', 'desc')->limit(8)->get();

        return view($this->activeTemplate . 'image_details', compact('pageTitle', 'image', 'relatedImages', 'seoContents', 'todayDownload', 'monthlyDownload', 'alreadyDownloaded'));
    }

    public function memberFollowers($username)
    {
        $member      = User::where('username', $username)->firstOrFail();
        $followerIds = Follow::where('following_id', $member->id)->pluck('user_id');
        $members     = User::whereIn('id', $followerIds)->withCount('images')->orderBy('images_count', 'DESC')->paginate(getPaginate());
        $heading     = $member->fullname . '\'s followers';
        $pageTitle   = 'Followers';

        return view($this->activeTemplate . 'member.all', compact('pageTitle', 'members', 'heading'));
    }

    public function memberFollowings($username)
    {
        $member = User::where('username', $username)->firstOrFail();
        $followingIds = Follow::where('user_id', $member->id)->pluck('user_id');
        $members = User::whereIn('id', $followingIds)->withCount('images')->orderBy('images_count', 'DESC')->paginate(getPaginate());
        $heading = $member->fullname . '\'s followings';
        $pageTitle = 'Followings';

        return view($this->activeTemplate . 'member.all', compact('pageTitle', 'members', 'heading'));
    }

    public function images($scope = null)
    {
        $pageTitle = 'Images';
        $images = Image::query();

        if ($scope) {
            try {
                $pageTitle = str_replace('-', ' ', ucwords($scope, '-')) . ' Images';
                $scope = lcfirst(str_replace('-', '', ucwords($scope, '-')));
                $images = $images->$scope();
            } catch (\Throwable $th) {
                abort(404);
            }
        }

        if (in_array($scope, ['popular', 'mostDownload'])) {
            $images = $images->active()->with('user', 'likes')->paginate(getPaginate());
        } else {
            $images = $images->active()->with('user', 'likes')->orderBy('id', 'DESC')->paginate(getPaginate());
        }

        return view($this->activeTemplate . 'premium_images', compact('pageTitle', 'images'));
    }

    public function search(Request $request)
    {
        $pageTitle = "Search";
        $images = collect([]);
        $collections = collect([]);
        if ($request->type == 'image') {
            $getImages = $this->getImages($request);
            $images = $getImages['images'];
            $imageCount = $getImages['imageCount'];
            $getCollections = $this->getCollections($request, true);
            $collectionCount = $getCollections['collectionCount'];
        } else {
            $getImages = $this->getImages($request, true);
            $imageCount = $getImages['imageCount'];
            $getCollections = $this->getCollections($request);
            $collections = $getCollections['collections'];
            $collectionCount = $getCollections['collectionCount'];
        }

        return view($this->activeTemplate . 'image_search', compact('pageTitle', 'images', 'collections', 'imageCount', 'collectionCount'));
    }

    private function getImages($request, $onlyCount = false)
    {
        $images = $this->searchImages($request);
        $data['imageCount'] = (clone $images)->count();
        if (!$onlyCount) {
            $data['images'] = $images->paginate(getPaginate(25));
        }
        return $data;
    }

    private function searchImages($request)
    {
        $images = Image::active()->whereHas('category', function ($query) {
            $query->where('status', Status::ENABLE);
        })->with('likes', 'user');

        if ($request->category) {
            $category = $request->category;
            $images = $images->whereHas('category', function ($query) use ($category) {
                $query->where('slug', $category)->where('status', Status::ENABLE);
            });
        }

        if ($request->has('is_free')) {
            $images = $images->where('is_free', $request->is_free);
        }

        if ($request->has('color')) {
            $images = $images->whereJsonContains('colors', $request->color);
        }

        if ($request->has('tag')) {
            $images = $images->whereJsonContains('tags', $request->tag);
        }

        if ($request->has('filter')) {
            $filter = $request->filter;
            $images = $images->where(function ($query) use ($filter) {
                $query->where('title', 'like', "%$filter%")->orWhere(function ($query) use ($filter) {
                    $query->whereJsonContains('tags', $filter);
                })->orWhere(function ($query) use ($filter) {
                    $query->whereHas('category', function ($category) use ($filter) {
                        $category->where('name', 'like', "%$filter%");
                    })->orWhereHas('user', function ($user) use ($filter) {
                        $user->where('username', 'like', "%$filter%")
                            ->orWhere('firstname', 'like', "%$filter%")
                            ->orWhere('lastname', 'like', "%$filter%");
                    })->orWhereHas('collections', function ($collections) use ($filter) {
                        $collections->where('title', 'like', "%$filter%");
                    });
                });
            });
        }


        //last filter
        if ($request->has('period')) {
            $images = $images->where('created_at', '>=', Carbon::now()->subMonth($request->period));
        }

        if ($request->has('popular')) {
            $images = $images->orderBy('total_downloads', 'desc');
        }

        if (!$request->has('sort_by')) {
            $images = $images->orderBy('id', 'desc');
        } else {
            $images = $images->orderBy('id', 'asc');
        }

        return $images;
    }

    private function getCollections($request, $onlyCount = false)
    {
        $collections = $this->searchCollections($request);
        $data['collectionCount'] = (clone $collections)->count();
        if (!$onlyCount) {
            $data['collections'] = $collections->paginate(getPaginate());
        }
        return $data;
    }

    private function searchCollections($request)
    {
        $collections = Collection::public()->with('images', 'user')->whereHas('images');

        if ($request->category) {
            $category = $request->category;
            $collections = $collections->whereHas('images', function ($images) use ($category) {
                $images->whereHas('category', function ($query) use ($category) {
                    $query->where('slug', $category);
                });
            });
        }

        if ($request->has('is_free')) {
            $isFree = $request->is_free;
            $collections = $collections->whereHas('images', function ($query) use ($isFree) {
                $query->where('is_free', $isFree);
            });
        }

        if ($request->has('color')) {
            $colors = $request->color;
            $collections = $collections->whereHas('images', function ($query) use ($colors) {
                $query->whereJsonContains('colors', $colors);
            });
        }

        if ($request->has('tag')) {
            $tags = $request->tags;
            $collections = $collections->whereHas('images', function ($query) use ($tags) {
                $query->whereJsonContains('tags', $tags);
            });
        }

        if ($request->has('filter')) {
            $filter = $request->filter;
            $collections = $collections->whereHas('images', function ($images) use ($filter) {
                $images->where(function ($query) use ($filter) {
                    $query->where('title', 'like', "%$filter%")->orWhere(function ($query) use ($filter) {
                        $query->whereJsonContains('tags', $filter);
                    })->orWhere(function ($query) use ($filter) {
                        $query->whereHas('category', function ($category) use ($filter) {
                            $category->where('name', 'like', "%$filter%");
                        })->orWhereHas('user', function ($user) use ($filter) {
                            $user->where('username', 'like', "%$filter%")
                                ->orWhere('firstname', 'like', "%$filter%")
                                ->orWhere('lastname', 'like', "%$filter%");
                        })->orWhereHas('collections', function ($collections) use ($filter) {
                            $collections->where('title', 'like', "%$filter%");
                        });
                    });
                });
            });
        }

        //last filter
        if ($request->has('period')) {
            $period = $request->period;
            $collections = $collections->whereHas('images', function ($query) use ($period) {
                $query->where('created_at', '>=', Carbon::now()->subMonth($period));
            });
        }

        if ($request->has('popular')) {
            $collections = $collections->withSum('images as total_downloads', 'total_downloads')->orderBy('total_downloads', 'DESC');
        }

        if (!$request->has('sort_by')) {
            $collections = $collections->orderBy('id', 'desc');
        } else {
            $collections = $collections->orderBy('id', 'asc');
        }

        return $collections;
    }

    protected function memberSeoContent($member)
    {
        $imagePath   = getFilePath('userProfile') . '/' . @$member->image;
        $keywords    = [$member->username, $member->firstname, $member->lastname, $member->fullname];
        $seoContents = getSeoContents($keywords, $member->fullname, $member->fullname, $imagePath, 'user');
        return $seoContents;
    }

    private function incrementTotalView($image)
    {
        $counter = session()->get('viewCounter');
        if (!isset($counter)) {
            $imageData = [$image->id => Carbon::now()->addMinutes(5)];
            session()->put('viewCounter', $imageData);

            $image->total_view += 1;
            $image->save();
        } elseif (!array_key_exists($image->id, $counter)) {
            $imageData = $counter + [$image->id => Carbon::now()->addMinutes(5)];
            session()->put('viewCounter', $imageData);
            $image->total_view += 1;
            $image->save();
        } else {
            if ($counter[$image->id] < Carbon::now()) {
                $image->total_view += 1;
                $image->save();

                $counter[$image->id] = Carbon::now()->addMinutes(5);
                session()->put('viewCounter', $counter);
            }
        }
    }
}
