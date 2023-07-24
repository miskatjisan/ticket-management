<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EarningLog extends Model
{
    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    public function contributor()
    {
        return $this->belongsTo(User::class, 'contributor_id');
    }
}
