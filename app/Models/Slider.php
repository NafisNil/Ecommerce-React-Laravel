<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class Slider extends Model
{
    protected $fillable = [
        'title', 'subtitle', 'link_url', 'image_path', 'active', 'sort_order',
    ];

    protected $appends = ['image_url'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function getImageUrlAttribute(): string
    {
        $path = $this->image_path ?? '';
        if (!$path) return asset('favicon.svg');
        // If already a full URL
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//')) {
            return $path;
        }
    return asset('storage/' . ltrim($path, '/'));
    }
}
