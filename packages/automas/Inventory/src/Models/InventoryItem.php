<?php

namespace Automas\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Automas\ProductService\Models\ProductServiceItem;
use Automas\Account\Models\ChartOfAccount;

class InventoryItem extends Model
{
    protected $fillable = [
        'product_id',
        'reorder_level',
        'maximum_level',
        'valuation_method',
        'is_tracked',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'reorder_level' => 'decimal:2',
        'maximum_level' => 'decimal:2',
        'is_tracked' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductServiceItem::class, 'product_id');
    }

    public function costLayers(): HasMany
    {
        return $this->hasMany(InventoryCostLayer::class, 'item_id');
    }

    public function cogsHistory(): HasMany
    {
        return $this->hasMany(InventoryCogsHistory::class, 'item_id');
    }
 
}
