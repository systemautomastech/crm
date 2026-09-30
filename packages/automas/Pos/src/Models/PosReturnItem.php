<?php

namespace Automas\Pos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Automas\ProductService\Models\ProductServiceItem;

class PosReturnItem extends Model
{
    protected $fillable = [
        'return_id',
        'product_id',
        'original_pos_item_id',
        'original_quantity',
        'return_quantity',
        'unit_price',
        'discount_percentage',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'reason'
    ];

    protected $casts = [
        'original_quantity' => 'integer',
        'return_quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2'
    ];

    public function posReturn(): BelongsTo
    {
        return $this->belongsTo(PosReturn::class, 'return_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductServiceItem::class, 'product_id');
    }

    public function originalPosItem(): BelongsTo
    {
        return $this->belongsTo(PosItem::class, 'original_pos_item_id');
    }

    public function taxes(): HasMany
    {
        return $this->hasMany(PosReturnItemTax::class, 'item_id');
    }
}
