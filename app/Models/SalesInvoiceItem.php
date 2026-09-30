<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesInvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'product_id',
        'description',
        'product_type',
        'quantity',
        'unit_price',
        'discount_type',
        'discount_percentage',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
        'total_amount',
        'creator_id',
        'created_by'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2'
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class, 'invoice_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(\Automas\ProductService\Models\ProductServiceItem::class, 'product_id');
    }

    public function taxes(): HasMany
    {
        return $this->hasMany(SalesInvoiceItemTax::class, 'item_id');
    }

    public function calculateAmounts()
    {
        $lineTotal = $this->quantity * $this->unit_price;
        if (($this->discount_type ?? 'percentage') === 'fixed') {
            $discountAmount = min(max((float) ($this->discount_amount ?? 0), 0), $lineTotal);
            $this->discount_amount = $discountAmount;
            $this->discount_percentage = $lineTotal > 0 ? round(($discountAmount / $lineTotal) * 100, 2) : 0;
        } else {
            $this->discount_type = 'percentage';
            $this->discount_amount = ($lineTotal * ($this->discount_percentage ?? 0)) / 100;
        }
        $afterDiscount = max(0, $lineTotal - $this->discount_amount);
        $this->tax_amount = ($afterDiscount * ($this->tax_percentage ?? 0)) / 100;
        $this->total_amount = $afterDiscount + $this->tax_amount;
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->calculateAmounts();
        });
    }
}