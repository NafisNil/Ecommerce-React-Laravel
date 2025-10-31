<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'active', 'min_order', 'usage_limit', 'used_count', 'starts_at', 'ends_at'
    ];

    protected $casts = [
        'active' => 'boolean',
        'value' => 'decimal:2',
        'min_order' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function isValidForTotal(float $total): bool
    {
        if (!$this->active) return false;
        if ($this->starts_at && now()->lt($this->starts_at)) return false;
        if ($this->ends_at && now()->gt($this->ends_at)) return false;
        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) return false;
        if ($this->min_order !== null && $total < (float)$this->min_order) return false;
        return true;
    }

    public function getDiscountForTotal(float $total): float
    {
        if ($this->type === 'percentage') {
            return round($total * ((float)$this->value) / 100, 2);
        }
        return min((float)$this->value, $total);
    }
}
