<?php

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, BelongsToMany};

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sku', 'name', 'description',
        'product_category_id', 'unit_of_measure_id',
        'cost_price', 'selling_price', 'reorder_point', 'is_active',
    ];

    protected $casts = [
        'cost_price'    => 'decimal:4',
        'selling_price' => 'decimal:4',
        'is_active'     => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function unitOfMeasure(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'product_warehouse')
                    ->withPivot('quantity_on_hand');
    }

    // Convenience: total stock across all warehouses
    public function totalStock(): float
    {
        return (float) $this->warehouses()->sum('quantity_on_hand');
    }

    // Stock in a specific warehouse (from cache)
    public function stockInWarehouse(int $warehouseId): float
    {
        $pivot = $this->warehouses()->wherePivot('warehouse_id', $warehouseId)->first();
        return $pivot ? (float) $pivot->pivot->quantity_on_hand : 0.0;
    }
}