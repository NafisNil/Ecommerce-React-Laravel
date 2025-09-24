<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductVariation extends Model
{
    protected $fillable = [
        'product_id', 'variation_type_option_ids', 'quantity', 'price', 'sku',
    ];

    protected $casts = [
        'variation_type_option_ids' => 'array',
        'quantity' => 'integer',
        'price' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted(): void
    {
        static::creating(function (self $variation) {
            if (empty($variation->sku)) {
                $product = $variation->relationLoaded('product') ? $variation->product : Product::find($variation->product_id);
                $variation->sku = static::generateUniqueSku($product, (array) $variation->variation_type_option_ids);
            }
        });
    }

    public static function generateUniqueSku(?Product $product = null, array $optionIds = []): string
    {
        $prefix = 'SKU';
        if ($product) {
            $slug = strtoupper(Str::slug($product->title ?? (string) $product->id, ''));
            $prefix = substr($slug, 0, 8) ?: 'SKU';
        }

        // Create a short deterministic-ish code from option ids + time to reduce collision risk
        $seed = json_encode(array_values($optionIds)) . '|' . microtime(true);
        $code = strtoupper(base_convert(sprintf('%u', crc32($seed)), 10, 36));
        $sku = $prefix . '-' . $code;

        // Ensure uniqueness; fallback to random code on rare collision
        $attempts = 0;
        while (self::where('sku', $sku)->exists() && $attempts < 5) {
            $sku = $prefix . '-' . strtoupper(Str::random(8));
            $attempts++;
        }

        return $sku;
    }
}
