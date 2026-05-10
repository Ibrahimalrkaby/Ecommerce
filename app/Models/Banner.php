<?php

namespace App\Models;

use App\Models\Concerns\HasPublicStoragePhoto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;
    use HasPublicStoragePhoto;

    protected $fillable = ['title', 'slug', 'photo', 'description', 'status'];

    protected $appends = ['photo_url'];

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->publicUrlForStored($this->photo);
    }

    public function deleteStoredPhotoIfExists(): void
    {
        $this->deleteStoredFileIfExists($this->photo);
    }
}
