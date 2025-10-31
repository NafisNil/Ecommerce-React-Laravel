<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
    return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'image' => $this->getFirstMediaUrl('products', 'thumb') ?: null,
            'images' => $this->getMedia('products')->map(function ($img) {
                return [
                    'id' => $img->id,
                    'thumb' => $img->hasGeneratedConversion('thumb') ? $img->getUrl('thumb') : $img->getUrl(),
                    'small' => $img->hasGeneratedConversion('small') ? $img->getUrl('small') : $img->getUrl(),
                    'medium' => $img->hasGeneratedConversion('medium') ? $img->getUrl('medium') : $img->getUrl(),
                    'large' => $img->hasGeneratedConversion('large') ? $img->getUrl('large') : $img->getUrl(),
                    'original' => $img->getUrl(),
                    'alt_text' => $img->custom_properties['alt_text'] ?? null,
                ];
            })->values()->toArray(),
            'user' =>[
                'id' => $this->user->id,
                'name' => $this->user->name,
                'shop_name' => optional($this->user->vendor)->shop_name,
            ],
            'department' => [
                'id' => $this->department->id,
                'name' => $this->department->name,
                'slug' => $this->department->slug,
            ],
            'variationTypes' => $this->variationTypes()->with('options')->get()->map(function ($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'type' => $type->type,
                    'options' => $type->options()->get()->map(function ($opt) {
                        return [
                            'id' => $opt->id,
                            'name' => $opt->name,
                            'images' => $opt->getMedia('option_images')->map(function ($img) {
                                return [
                                    'id' => $img->id,
                                    'thumb' => $img->hasGeneratedConversion('thumb') ? $img->getUrl('thumb') : $img->getUrl(),
                                    'small' => $img->hasGeneratedConversion('small') ? $img->getUrl('small') : $img->getUrl(),
                                    'medium' => $img->hasGeneratedConversion('medium') ? $img->getUrl('medium') : $img->getUrl(),
                                    'large' => $img->hasGeneratedConversion('large') ? $img->getUrl('large') : $img->getUrl(),
                                    'original' => $img->getUrl(),
                                    'alt_text' => $img->custom_properties['alt_text'] ?? null,
                                ];
                            })->values()->toArray(),
                        ];
                    })->values()->toArray(),
                ];
            })->values()->toArray(),
            'variations' => $this->variations()->get()->map(function ($var) {
                return [
                    'id' => $var->id,
                    'price' => $var->price,
                    'quantity' => $var->quantity,
                    'variation_type_option_ids' => $var->variation_type_option_ids,
                ];
            })->values()->toArray(),
            'average_rating' => round((float) ($this->ratings()->avg('rating') ?? 0), 2),
            'ratings_count' => (int) $this->ratings()->count(),
        ];
    }
}
