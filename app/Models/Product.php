<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Enums\VendorStatusEnum;
use App\Enums\ProductStatusEnum;

class Product extends Model implements HasMedia
{
    //
    use InteractsWithMedia;
    protected $fillable = [
        'title', 'slug', 'department_id', 'category_id', 'description', 'price', 'quantity', 'status',
        'is_offered', 'is_featured', 'offered_price'
    ];

    protected $casts = [
        'is_offered' => 'boolean',
        'is_featured' => 'boolean',
        'offered_price' => 'decimal:2',
        // no JSON variation_types now; using related tables variation_types & variation_type_options
    ];
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(368)
            ->height(232)
            ->sharpen(10)
            ->nonQueued();
        $this->addMediaConversion('small')
            ->width(480)
            ->height(360)
            ->sharpen(10)
            ->nonQueued();
        $this->addMediaConversion('medium')
            ->width(640)
            ->height(480)
            ->sharpen(10)
            ->nonQueued();
        $this->addMediaConversion('large')
            ->width(800)
            ->height(600)
            ->sharpen(10)
            ->nonQueued();
        
    }
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('products')
            ->useDisk('public')
            ->useFallbackUrl('/favicon.svg')
            ->withResponsiveImages();
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function variationTypes()
    {
        return $this->hasMany(VariationType::class);
    }

    public function scopeForVendor(Builder $query) : Builder
    {
    $userId = Auth::id();
    return $query->when($userId, fn($q) => $q->where('created_by', $userId));
    }



    public function scopePublished(Builder $query) : Builder
    {
        return $query->where('status', ProductStatusEnum::PUBLISHED->value);
    }

    public function scopeForWebsite(Builder $query) : Builder
    {
        return $query->where('products.status', ProductStatusEnum::PUBLISHED->value)
                     ->where('quantity', '>', 0)->vendorApproved();
    }

    public function scopeVendorApproved(Builder $query){
        return $query->join('vendors', 'vendors.user_id', '=', 'products.created_by')
                     ->where('vendors.status', '=', VendorStatusEnum::Approved->value);
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class, 'product_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ratings()
    {
        return $this->hasMany(ProductRating::class);
    }

    public function getPriceForOptions($optionIds = []){
        $optionIds = array_values($optionIds);
        sort($optionIds);
        foreach ($this->variations as $key => $variation) {
            $a = $variation->variation_type_option_ids;
            sort($a);
            if ($a === $optionIds) {
                return $variation->price ?? $this->price;
            }
        }
        return $this->price;
        // return $basePrice + $additionalPrice;

    }

    public function getImageForOptions(array|string|null $optionIds = [])
    {
        // Normalize input to an array of IDs
        if (is_string($optionIds)) {
            $decoded = json_decode($optionIds, true);
            $optionIds = is_array($decoded) ? $decoded : [];
        }

        $ids = array_values(is_array($optionIds) ? $optionIds : []);
        if (!empty($ids)) {
            sort($ids);
            $options = VariationTypeOption::whereIn('id', $ids)->get();
            foreach ($options as $option) {
                // Use the correct collection name defined in VariationTypeOption
                $image = $option->getFirstMediaUrl('option_images', 'small');
                if ($image) {
                    return $image;
                }
            }
        }
        return $this->getFirstMediaUrl('products', 'small');
        // foreach ($this->variations as $key => $variation) {
        //     $a = $variation->variation_type_option_ids;
        //     sort($a);
        //     if ($a === $optionIds) {
        //         if ($variation->image) {
        //             return $variation->getFirstMediaUrl('variations', 'small') ?: $this->getFirstMediaUrl('products', 'small');
        //         }else{
        //             return $this->getFirstMediaUrl('products', 'small');
        //         }
        //     }
        // }
        // return $this->getFirstMediaUrl('products', 'small');
        // return $basePrice + $additionalPrice;

    }
    
}
