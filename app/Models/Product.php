<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'image_path',
        'price',
        'badge',
        'has_cooking_options',
        'cooking_options',
        'is_available',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'has_cooking_options' => 'boolean',
            'cooking_options' => 'array',
            'is_available' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('order');
    }

    public function hasVariants(): bool
    {
        return $this->variants()->count() > 0;
    }

    public function hasCookingOptions(): bool
    {
        return (bool) $this->has_cooking_options;
    }

    public function getCookingOptionsList(): array
    {
        if (!$this->has_cooking_options) {
            return [];
        }

        if (!empty($this->cooking_options) && is_array($this->cooking_options)) {
            return array_values(array_unique(array_map(function ($opt) {
                return ($opt === 'Al Horno' || $opt === 'Horno') ? 'Horno' : $opt;
            }, $this->cooking_options)));
        }

        return ['Horno', 'Frita'];
    }

    public function getFormattedPriceAttribute(): string
    {
        if ($this->hasVariants() && $this->variants->isNotEmpty()) {
            return '$' . number_format($this->variants->first()->price, 0, ',', '.');
        }

        if ($this->price !== null) {
            return '$' . number_format($this->price, 0, ',', '.');
        }

        return '';
    }
}
