<?php

namespace Automas\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryCostLayer extends Model
{
    protected $fillable = [
        'item_id',
        'warehouse_id',
        'purchase_date',
        'quantity',
        'unit_cost',
        'remaining_quantity',
        'reference_type',
        'reference_id',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'remaining_quantity' => 'decimal:2',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }
}
