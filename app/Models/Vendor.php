<?php

namespace App\Models;

use App\Enums\VendorStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use function asset;

class Vendor extends Model
{
    protected $fillable = [
        'user_id',
        'shop_name',
        'shop_address',
        'shop_description',
        'cover_image',
    ];

    protected $primaryKey = 'user_id';

    protected $appends = [
        'cover_image_url',
    ];

    public function scopeEligibleForPayout(Builder $query)
    {
        return $query->where('status', VendorStatusEnum::Approved);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->cover_image ? asset('storage/' . ltrim($this->cover_image, '/')) : null;
    }
}
