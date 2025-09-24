<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VariationType extends Model
{
    //
    public $timestamps = false;
    protected $fillable = [
        'product_id', 'name', 'type',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function options()
    {
        return $this->hasMany(VariationTypeOption::class, 'variation_type_id');
    }
}
