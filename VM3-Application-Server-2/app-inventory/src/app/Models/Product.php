<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A product held in stock.
 *
 * @property string $sku
 * @property string $name
 * @property int $quantity
 * @property int $reorder_level
 * @property string $unit_price
 * @property string|null $location
 */
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'quantity',
        'reorder_level',
        'unit_price',
        'location',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reorder_level' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    /**
     * Products at or below their reorder level.
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('quantity', '<=', 'reorder_level');
    }

    /**
     * Whether this product needs to be reordered.
     */
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->reorder_level;
    }

    /**
     * Value of the stock held for this product.
     */
    public function stockValue(): float
    {
        return round($this->quantity * (float) $this->unit_price, 2);
    }
}
