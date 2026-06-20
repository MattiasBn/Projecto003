<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    protected $fillable = ['follower_id', 'following_id'];

    public function seguidor()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    public function seguido()
    {
        return $this->belongsTo(User::class, 'following_id');
    }
}