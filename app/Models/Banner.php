<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'photo', 'description', 'status'];

    protected $appends = ['photo_url'];

    public function getPhotoUrlAttribute(): string
    {
        if (!$this->photo) {
            return asset('backend/img/avatar.png');
        }

        $photoPath = trim(parse_url($this->photo, PHP_URL_PATH) ?: $this->photo);
        $relativePath = ltrim($photoPath, '/');

        if (Str::startsWith($this->photo, ['http://', 'https://']) && file_exists(public_path($relativePath))) {
            return asset($relativePath);
        }

        if (file_exists(public_path($relativePath))) {
            return asset($relativePath);
        }

        return asset('backend/img/avatar.png');
    }
}
