<?php

namespace App\Models;

use App\Models\Concerns\HasPublicStoragePhoto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    use HasPublicStoragePhoto;

    protected $fillable = ['title', 'slug', 'summary', 'description', 'cat_id', 'child_cat_id', 'price', 'brand_id', 'discount', 'status', 'photo', 'size', 'stock', 'is_featured', 'condition'];

    protected $appends = ['photo_url', 'photo_urls'];

    /**
     * First image URL (product.photo may be comma-separated paths).
     */
    public function getPhotoUrlAttribute(): ?string
    {
        $paths = $this->photoPathsList();
        if ($paths === []) {
            return $this->publicUrlForStored(null);
        }

        return $this->publicUrlForStored($paths[0]);
    }

    /**
     * @return list<string>
     */
    public function getPhotoUrlsAttribute(): array
    {
        $paths = $this->photoPathsList();
        if ($paths === []) {
            return [$this->publicUrlForStored(null)];
        }

        return array_map(fn (string $p) => $this->publicUrlForStored($p), $paths);
    }

    /**
     * @return list<string>
     */
    protected function photoPathsList(): array
    {
        if ($this->photo === null || trim((string) $this->photo) === '') {
            return [];
        }

        $parts = array_map('trim', explode(',', (string) $this->photo));

        return array_values(array_filter($parts, fn (string $p) => $p !== ''));
    }

    public function deleteStoredPhotoIfExists(): void
    {
        foreach ($this->photoPathsList() as $path) {
            $this->deleteStoredFileIfExists($path);
        }
    }

    /**
     * Normalize comma-separated paths from request input.
     */
    public function normalizeCommaSeparatedPhotoPaths(string $rawInput): string
    {
        $rawInput = trim($rawInput);
        if ($rawInput === '') {
            return '';
        }

        $segments = array_map('trim', explode(',', $rawInput));
        $normalized = [];
        foreach ($segments as $segment) {
            if ($segment === '') {
                continue;
            }
            $normalized[] = $this->normalizeIncomingPhotoString($segment);
        }

        return implode(',', $normalized);
    }

    public function cat_info()
    {
        return $this->hasOne('App\Models\Category', 'id', 'cat_id');
    }

    public function sub_cat_info()
    {
        return $this->hasOne('App\Models\Category', 'id', 'child_cat_id');
    }

    public static function getAllProduct()
    {
        return Product::with(['cat_info', 'sub_cat_info'])->orderBy('id', 'desc')->paginate(10);
    }

    public function rel_prods()
    {
        return $this->hasMany('App\Models\Product', 'cat_id', 'cat_id')->where('status', 'active')->orderBy('id', 'DESC')->limit(8);
    }

    public static function getProductBySlug($slug)
    {
        return Product::with(['cat_info', 'rel_prods', 'getReview'])->where('slug', $slug)->first();
    }

    public static function countActiveProduct()
    {
        $data = Product::where('status', 'active')->count();
        if ($data) {
            return $data;
        }
        return 0;
    }

    public function brand()
    {
        return $this->hasOne(Brand::class, 'id', 'brand_id');
    }

    public function carts()
    {
        return $this->hasMany(Cart::class)->whereNotNull('cart_id');
    }

    public function getReview()
    {
        return $this->hasMany('App\Models\ProductReview', 'product_id', 'id')->with('user_info')->where('status', 'active')->orderBy('id', 'DESC');
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }
}
