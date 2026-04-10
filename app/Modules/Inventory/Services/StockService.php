<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function recordMovement(
        Product   $product,
        Warehouse $warehouse,
        string    $type,
        float     $quantity,
        ?string   $notes = null,
        ?object   $reference = null
    ): StockMovement {

        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than zero.');
        }

        return DB::transaction(function () use ($product, $warehouse, $type, $quantity, $notes, $reference) {

            if (in_array($type, StockMovement::TYPES_OUT)) {
                $this->ensureSufficientStock($product, $warehouse, $quantity);
            }

            $movement = StockMovement::create([
                'product_id'     => $product->id,
                'warehouse_id'   => $warehouse->id,
                'type'           => $type,
                'quantity'       => $quantity,
                'notes'          => $notes,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id'   => $reference?->id,
                'created_by'     => Auth::id(),
            ]);

            $this->updateStockCache($product, $warehouse, $type, $quantity);

            return $movement;
        });
    }

    public function transferStock(
        Product   $product,
        Warehouse $fromWarehouse,
        Warehouse $toWarehouse,
        float     $quantity,
        ?string   $notes = null
    ): array {

        if ($fromWarehouse->id === $toWarehouse->id) {
            throw new \InvalidArgumentException('Source and destination warehouses must differ.');
        }

        return DB::transaction(function () use ($product, $fromWarehouse, $toWarehouse, $quantity, $notes) {
            $out = $this->recordMovement($product, $fromWarehouse, 'transfer_out', $quantity, $notes);
            $in  = $this->recordMovement($product, $toWarehouse,   'transfer_in',  $quantity, $notes);
            return [$out, $in];
        });
    }

    public function adjustStock(
        Product   $product,
        Warehouse $warehouse,
        float     $newQuantity,
        ?string   $notes = null
    ): ?StockMovement {

        $currentStock = $this->getStock($product, $warehouse);
        $delta        = $newQuantity - $currentStock;

        if ($delta == 0) {
            return null;
        }

        $type     = $delta > 0 ? 'in' : 'out';
        $quantity = abs($delta);

        return DB::transaction(function () use ($product, $warehouse, $type, $quantity, $notes, $newQuantity) {

            $movement = StockMovement::create([
                'product_id'   => $product->id,
                'warehouse_id' => $warehouse->id,
                'type'         => 'adjustment',
                'quantity'     => $quantity,
                'notes'        => $notes ?? 'Stock adjustment',
                'created_by'   => Auth::id(),
            ]);

            // For adjustments, set to exact value rather than adding delta
            DB::statement("
                INSERT INTO product_warehouse (product_id, warehouse_id, quantity_on_hand)
                VALUES (:product_id, :warehouse_id, :quantity)
                ON CONFLICT (product_id, warehouse_id)
                DO UPDATE SET quantity_on_hand = :quantity2
            ", [
                'product_id'   => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity'     => $newQuantity,
                'quantity2'    => $newQuantity,
            ]);

            return $movement;
        });
    }

    public function getStock(Product $product, Warehouse $warehouse): float
    {
        $value = DB::table('product_warehouse')
            ->where('product_id',   $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->value('quantity_on_hand');

        return (float) ($value ?? 0.0);
    }

    public function recalculateStock(Product $product, Warehouse $warehouse): float
    {
        $inbound = (float) StockMovement::where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->whereIn('type', StockMovement::TYPES_IN)
            ->sum('quantity');

        $outbound = (float) StockMovement::where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->whereIn('type', StockMovement::TYPES_OUT)
            ->sum('quantity');

        $calculated = $inbound - $outbound;

        DB::statement("
            INSERT INTO product_warehouse (product_id, warehouse_id, quantity_on_hand)
            VALUES (:product_id, :warehouse_id, :quantity)
            ON CONFLICT (product_id, warehouse_id)
            DO UPDATE SET quantity_on_hand = :quantity2
        ", [
            'product_id'   => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity'     => $calculated,
            'quantity2'    => $calculated,
        ]);

        return $calculated;
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function ensureSufficientStock(Product $product, Warehouse $warehouse, float $quantity): void
    {
        $available = $this->getStock($product, $warehouse);

        if ($quantity > $available) {
            throw ValidationException::withMessages([
                'quantity' => "Insufficient stock. Available: {$available}, Requested: {$quantity}.",
            ]);
        }
    }

    private function updateStockCache(Product $product, Warehouse $warehouse, string $type, float $quantity): void
    {
        $delta = in_array($type, StockMovement::TYPES_IN) ? $quantity : -$quantity;

        DB::statement("
            INSERT INTO product_warehouse (product_id, warehouse_id, quantity_on_hand)
            VALUES (:product_id, :warehouse_id, :initial)
            ON CONFLICT (product_id, warehouse_id)
            DO UPDATE SET quantity_on_hand = product_warehouse.quantity_on_hand + :delta
        ", [
            'product_id'   => $product->id,
            'warehouse_id' => $warehouse->id,
            'initial'      => max(0, $delta),
            'delta'        => $delta,
        ]);
    }
}