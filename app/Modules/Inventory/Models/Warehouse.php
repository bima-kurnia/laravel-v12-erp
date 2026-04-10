<?php

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{HasMany, BelongsToMany};

class Warehouse extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'name', 'address', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_warehouse')
                    ->withPivot('quantity_on_hand');
    }
}