<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostTag extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'slug', 'status'];

    public function post()
    {
        return $this->hasMany('App\Models\Post', 'post_cat_id', 'id')->where('status', 'active');
    }
}
