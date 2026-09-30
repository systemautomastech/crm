<?php

namespace Automas\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryCogsHistory extends Model
{
    protected $table = 'inventory_cogs_history';

    protected $fillable = [
        'item_id',
        'warehouse_id',
        'sale_date',
        'quantity_sold',
        'unit_cost',
        'total_cogs',
        'reference_type',
        'reference_id',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'quantity_sold' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cogs' => 'decimal:2',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }
}
