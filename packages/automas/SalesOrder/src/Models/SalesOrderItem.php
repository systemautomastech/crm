<?php

namespace Automas\SalesOrder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOrderItem extends Model
{
    use HasFactory;

    protected $table = 'sales_order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'product_type',
        'quantity',
        'unit_price',
        'discount_type',
        'discount_percentage',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
        'final_price',
        'total_amount',
        'description',
        'unit',
        'creator_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity'            => 'integer',
            'unit_price'          => 'decimal:2',
            'discount_percentage' => 'decimal:2',
            'discount_amount'     => 'decimal:2',
            'tax_percentage'      => 'decimal:2',
            'tax_amount'          => 'decimal:2',
            'final_price'         => 'decimal:2',
            'total_amount'        => 'decimal:2',
        ];
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'order_id');
    }

    public function product(): BelongsTo
    {
        if (class_exists(\Automas\ProductService\Models\ProductServiceItem::class)) {
            return $this->belongsTo(\Automas\ProductService\Models\ProductServiceItem::class, 'product_id');
        }
        return $this->belongsTo(\App\Models\User::class, 'product_id'); // fallback stub
    }

    public function taxes(): HasMany
    {
        return $this->hasMany(SalesOrderItemTax::class, 'item_id');
    }

    public function deliveryItems(): HasMany
    {
        return $this->hasMany(SalesOrderDeliveryItem::class, 'sales_order_item_id');
    }

    /**
     * Total quantity delivered (from delivered final deliveries).
     */
    public function getDeliveredQuantityAttribute(): int
    {
        return (int) $this->deliveryItems()
            ->whereHas('delivery', fn($q) => $q->where('status', SalesOrderDelivery::STATUS_DELIVERED))
            ->sum('quantity');
    }

    /**
     * Remaining quantity available to deliver.
     */
    public function getRemainingQuantityAttribute(): int
    {
        return max(0, (int) $this->quantity - $this->delivered_quantity);
    }
}