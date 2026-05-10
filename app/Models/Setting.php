<?php

namespace App\Models;

use App\Models\Concerns\HasPublicStoragePhoto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    use HasPublicStoragePhoto;

    protected $fillable = ['short_des', 'description', 'photo', 'address', 'phone', 'email', 'logo'];

    protected $appends = ['photo_url', 'logo_url'];

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->publicUrlForStored($this->photo);
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->publicUrlForStored($this->logo);
    }

    public function deleteStoredPhotoIfExists(): void
    {
        $this->deleteStoredFileIfExists($this->photo);
    }

    public function deleteStoredLogoIfExists(): void
    {
        $this->deleteStoredFileIfExists($this->logo);
    }
}
