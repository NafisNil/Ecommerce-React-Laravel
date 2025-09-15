<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    //
    use InteractsWithMedia;
    protected $fillable = [
        'title', 'slug', 'department_id', 'category_id', 'description', 'price', 'quantity', 'status', 'variation_types'
    ];

    protected $casts = [
        'variation_types' => 'array',
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
}
