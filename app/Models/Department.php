<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


class Department extends Model
{
    //

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function scopePublished(Builder $query):Builder
    {
        return $query->where('active', true);
    }
}
