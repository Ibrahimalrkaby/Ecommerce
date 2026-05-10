<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Strip query string and domain from LFM / pasted URLs; keep path for DB normalization.
     */
    protected function normalizePhotoPath(?string $photo): string
    {
        if ($photo === null || $photo === '') {
            return '';
        }

        $photo = trim($photo);

        return parse_url($photo, PHP_URL_PATH) ?: $photo;
    }
}
