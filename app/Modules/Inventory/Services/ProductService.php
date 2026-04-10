<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\Product;
use Illuminate\Support\Str;

class ProductService
{
    /**
     * Generate a unique SKU. Called on product creation if user leaves it blank.
     */
    public function generateSku(string $prefix = 'PRD'): string
    {
        do {
            $sku = strtoupper($prefix) . '-' . strtoupper(Str::random(6));
        } while (Product::where('sku', $sku)->exists());

        return $sku;
    }

    /**
     * Get products below reorder point per warehouse.
     */
    public function getLowStockProducts(): \Illuminate\Support\Collection
    {
        // Use a subquery-safe approach — return only IDs
        return \Illuminate\Support\Facades\DB::table('product_warehouse as pw')
            ->join('products', 'products.id', '=', 'pw.product_id')
            ->whereColumn('pw.quantity_on_hand', '<=', 'products.reorder_point')
            ->where('products.is_active', true)
            ->whereNull('products.deleted_at')
            ->pluck('products.id');
    }
}