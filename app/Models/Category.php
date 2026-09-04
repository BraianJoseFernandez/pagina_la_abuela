<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'icon_svg',
        'subtitle',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class)->orderBy('order');
    }

    public function activeProducts(): HasMany
    {
        return $this->hasMany(Product::class)->where('is_available', true)->orderBy('order');
    }

    public function images(): HasMany
    {
        return $this->hasMany(CategoryImage::class)->orderBy('order');
    }

    public function visibleImages(): HasMany
    {
        return $this->hasMany(CategoryImage::class)->where('is_visible', true)->orderBy('order');
    }
}
