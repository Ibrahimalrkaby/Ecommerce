<?php

namespace App\Models;

use App\Models\Concerns\HasPublicStoragePhoto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    use HasPublicStoragePhoto;

    protected $fillable = ['name', 'message', 'email', 'phone', 'read_at', 'photo', 'subject'];

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
