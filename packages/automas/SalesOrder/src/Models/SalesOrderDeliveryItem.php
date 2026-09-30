<?php

namespace Automas\SalesOrder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrderDeliveryItem extends Model
{
    protected $table = 'sales_order_delivery_items';

    protected $fillable = [
        'delivery_id',
        'sales_order_item_id',
        'product_id',
        'quantity',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(SalesOrderDelivery::class, 'delivery_id');
    }

    public function salesOrderItem(): BelongsTo
    {
        return $this->belongsTo(SalesOrderItem::class, 'sales_order_item_id');
    }

    public function product(): BelongsTo
    {
        if (class_exists(\Automas\ProductService\Models\ProductServiceItem::class)) {
            return $this->belongsTo(\Automas\ProductService\Models\ProductServiceItem::class, 'product_id');
        }
        return $this->belongsTo(\App\Models\User::class, 'product_id');
    }
}
