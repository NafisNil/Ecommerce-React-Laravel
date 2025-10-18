<?php

namespace App\Models;

use App\Enums\VendorStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Vendor extends Model
{
    protected $fillable = [
        'user_id',
        'shop_name',
        'shop_address',
        'shop_description',
    ];

    protected $primaryKey = 'user_id';

    public function scopeEligibleForPayout(Builder $query)
    {
        return $query->where('status', VendorStatusEnum::Approved);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
