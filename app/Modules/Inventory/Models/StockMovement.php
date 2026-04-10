<?php

namespace App\Modules\Inventory\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, MorphTo};

class StockMovement extends Model
{
    // Movements are immutable — no updates, no deletes
    protected $fillable = [
        'product_id', 'warehouse_id', 'type',
        'quantity', 'reference_type', 'reference_id',
        'notes', 'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    const TYPES_IN  = ['in', 'transfer_in', 'return_in', 'adjustment'];
    const TYPES_OUT = ['out', 'transfer_out', 'return_out'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function isInbound(): bool
    {
        return in_array($this->type, self::TYPES_IN);
    }
}