<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class VariationTypeOption extends Model implements HasMedia
{
    //
    use InteractsWithMedia;
    public function registerMediaConversions(Media $media = null): void
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
}
