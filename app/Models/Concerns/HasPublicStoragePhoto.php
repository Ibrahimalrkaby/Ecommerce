<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasPublicStoragePhoto
{
    public function publicUrlForStored(?string $stored): string
    {
        if ($stored === null || trim($stored) === '') {
            return asset('backend/img/thumbnail-default.jpg');
        }

        $stored = trim($stored);

        if (Str::startsWith($stored, ['http://', 'https://'])) {
            return $stored;
        }

        $relative = $this->normalizeStoredPath($stored);

        if ($relative === '') {
            return asset('backend/img/thumbnail-default.jpg');
        }

        return asset('storage/' . $relative);
    }

    /**
     * One LFM / pasted value → normalized relative path on the public disk.
     */
    public function normalizeIncomingPhotoString(string $photo): string
    {
        $photo = trim($photo);
        if ($photo === '') {
            return '';
        }

        $path = parse_url($photo, PHP_URL_PATH) ?: $photo;

        return $this->normalizeStoredPath($path);
    }

    /**
     * Path relative to storage/app/public (e.g. banners/foo.png or photos/1/foo.jpg).
     */
    public function normalizeStoredPath(string $photo): string
    {
        $photo = str_replace('\\', '/', trim($photo));

        if (Str::contains($photo, '/storage/')) {
            return ltrim(Str::after($photo, '/storage/'), '/');
        }

        if (Str::startsWith($photo, 'storage/')) {
            return ltrim(Str::after($photo, 'storage/'), '/');
        }

        return ltrim($photo, '/');
    }

    public function deleteStoredFileIfExists(?string $storedValue): void
    {
        if ($storedValue === null || trim($storedValue) === '') {
            return;
        }

        $storedValue = trim($storedValue);

        if (Str::startsWith($storedValue, ['http://', 'https://'])) {
            return;
        }

        $relative = $this->normalizeStoredPath($storedValue);

        if ($relative !== '' && Storage::disk('public')->exists($relative)) {
            Storage::disk('public')->delete($relative);
        }
    }
}
