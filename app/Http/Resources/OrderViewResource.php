<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderViewResource extends JsonResource
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
            'order_number' => $this->order_number,
            'status' => $this->status,
            'total_amount' => $this->total_amount,
            'total_price' => $this->total_price,
            'created_at' => $this->created_at->toDateTimeString(),
             'vendorUser' => $this->vendorUser ? (new VendorUserResource($this->vendorUser))->toArray($request) : null,
             'orderItems' => $this->orderItems->map(function ($item) {
                 return [
                     'id' => $item->id,
                  
                     'quantity' => $item->quantity,
                     'price' => $item->price,
                     'variation_type_option_ids' => $item->variation_type_option_ids,
                     'product' => [
                            'id' => $item->product->id,
                            'title' => $item->product->title,
                            'slug' => $item->product->slug,
                            'description' => $item->product->description,
                            'image' => $item->product->getImageForOptions($item->variation_type_option_ids ?? []),
                            // Add other product fields as necessary
                     ]
                     // Add other fields as necessary
                 ];
             })->values()->toArray(),
        ];
    }
}
