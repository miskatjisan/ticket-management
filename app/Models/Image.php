<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Image extends Model
{
    use Searchable;

    protected $casts = [
        'tags'       => 'array',
        'extensions' => 'array',
        'colors'     => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(Reviewer::class);
    }
    public function collections()
    {
        return $this->belongsToMany(Collection::class, 'collection_images', 'image_id', 'collection_id');
    }

    public function downloads()
    {
        return $this->hasMany(Download::class);
    }

    //scope
    public function scopePending($query)
    {
        $query->where('status', 0);
    }

    public function scopeApproved($query)
    {
        $query->where('status', 1);
    }

    public function scopeActive($query)
    {
        $query->where('status', 1)->where('is_active', 1);
    }

    public function scopeRejected($query)
    {
        $query->where('status', 3);
    }

    public function scopePremium($query)
    {
        $query->where('is_free', 0);
    }

    public function scopeFeatured($query)
    {
        $query->where('is_featured', 1);
    }

    public function scopePopular($query)
    {
        $query->where('total_view', '>', 0)->orderBy('total_view', 'DESC');
    }

    public function scopeMostDownload($query)
    {
        $query->where('total_downloads', '>', 0)->orderBy('total_downloads', 'DESC');
    }

    public function statusBadge(): Attribute
    {
        return new Attribute(
            get: fn () => $this->badgeData(),
        );
    }

    public function badgeData()
    {
        $html = '';
        if ($this->status == 1) {
            $html = '<span><span class="badge badge--success">' . trans('Approved') . '</span>';
        } elseif ($this->status == 3) {
            $html = '<span><span class="badge badge--danger">' . trans('Rejected') . '</span>';
        } else {
            $html = '<span><span class="badge badge--warning">' . trans('Pending') . '</span></span>';
        }
        return $html;
    }
}
