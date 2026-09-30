<?php

namespace Automas\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAdjustmentItem extends Model
{
    protected $fillable = [
        'adjustment_id',
        'item_id',
        'current_quantity',
        'adjusted_quantity',
        'difference_quantity',
        'notes',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'current_quantity' => 'decimal:2',
        'adjusted_quantity' => 'decimal:2',
        'difference_quantity' => 'decimal:2',
    ];

    public function adjustment(): BelongsTo
    {
        return $this->belongsTo(InventoryAdjustment::class, 'adjustment_id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }


}
