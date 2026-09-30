<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesProposalItem extends Model
{
    protected $fillable = [
        'proposal_id',
        'product_id',
        'section',
        'product_type',
        'description',
        'quantity',
        'unit_price',
        'discount_type',
        'discount_percentage',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
        'total_amount'
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

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(SalesProposal::class, 'proposal_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(\Automas\ProductService\Models\ProductServiceItem::class, 'product_id');
    }

    public function taxes(): HasMany
    {
        return $this->hasMany(SalesProposalItemTax::class, 'item_id');
    }

    public function calculateAmounts()
    {
        $lineTotal = $this->quantity * $this->unit_price;
        if ($this->discount_type === 'fixed') {
            $this->discount_amount = min($lineTotal, max(0, (float) $this->discount_amount));
            $this->discount_percentage = $lineTotal > 0 ? round(($this->discount_amount / $lineTotal) * 100, 4) : 0;
        } else {
            $this->discount_amount = ($lineTotal * $this->discount_percentage) / 100;
        }
        $afterDiscount = max(0, $lineTotal - $this->discount_amount);
        $this->tax_amount = ($afterDiscount * $this->tax_percentage) / 100;
        $this->total_amount = max(0, $afterDiscount + $this->tax_amount);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->calculateAmounts();
        });
    }
}