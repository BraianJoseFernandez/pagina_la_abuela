<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'category_name',
        'product_name',
        'variant_name',
        'cooking_method',
        'unit_price',
        'quantity',
        'subtotal',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'quantity' => 'integer',
            'subtotal' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getCategoryNameAttribute(?string $value): ?string
    {
        if (!empty($value)) {
            return $value;
        }

        if ($this->product && $this->product->category) {
            return $this->product->category->name;
        }

        if (!empty($this->product_name)) {
            $matchedProduct = Product::with('category')->where('name', $this->product_name)->first();
            if ($matchedProduct && $matchedProduct->category) {
                return $matchedProduct->category->name;
            }
        }

        return null;
    }
}
