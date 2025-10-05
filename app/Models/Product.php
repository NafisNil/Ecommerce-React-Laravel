<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Enums\ProductStatusEnum;

class Product extends Model implements HasMedia
{
    //
    use InteractsWithMedia;
    protected $fillable = [
        'title', 'slug', 'department_id', 'category_id', 'description', 'price', 'quantity', 'status'
    ];

    protected $casts = [
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
        return $query->where('created_by', auth()->user()->id);
    }

    public function scopePublished(Builder $query) : Builder
    {
        return $query->where('status', ProductStatusEnum::PUBLISHED->value);
    }

    public function scopeForWebsite(Builder $query) : Builder
    {
        return $query->where('status', ProductStatusEnum::PUBLISHED->value)
                     ->where('quantity', '>', 0);
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class, 'product_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'created_by');
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
    
}
