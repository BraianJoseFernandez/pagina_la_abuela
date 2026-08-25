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
        'is_available',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
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
