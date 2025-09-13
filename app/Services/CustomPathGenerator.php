<?php

namespace App\Services;

use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class CustomPathGenerator implements PathGenerator
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getPath(\Spatie\MediaLibrary\MediaCollections\Models\Media $media): string
    {
        // Implement your custom logic here
        return md5($media->id . config('app.key')) . '/';
    }

    public function getPathForConversions(\Spatie\MediaLibrary\MediaCollections\Models\Media $media): string
    {
        // Implement your custom logic here
        return $this->getPath($media) . 'conversions/';
    }

    public function getPathForResponsiveImages(\Spatie\MediaLibrary\MediaCollections\Models\Media $media): string
    {
        // Implement your custom logic here
        return $this->getPath($media) . 'responsive-images/';
    }
}
