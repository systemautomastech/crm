<?php

namespace Automas\SalesOrder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrderItemTax extends Model
{
    protected $table = 'sales_order_item_taxes';

    protected $fillable = [
        'item_id',
        'tax_name',
        'tax_rate',
    ];

    protected function casts(): array
    {
        return [
            'tax_rate' => 'decimal:4',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(SalesOrderItem::class, 'item_id');
    }
}
