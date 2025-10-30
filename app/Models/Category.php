<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    //
    protected $fillable = [
        'name', 'slug', 'department_id', 'parent_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (Category $category) {
            if (empty($category->slug) && !empty($category->name)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function (Category $category) {
            // If name changes and slug not explicitly set, regenerate slug
            if ($category->isDirty('name') && !$category->isDirty('slug')) {
                $category->slug = Str::slug($category->name);
            }
        });
    }
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
}
