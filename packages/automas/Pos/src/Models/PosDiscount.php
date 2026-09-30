<?php

namespace Automas\Pos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Automas\ProductService\Models\ProductServiceItem;
use Automas\ProductService\Models\ProductServiceCategory;

class PosDiscount extends Model
{
    protected $fillable = [
        'name',
        'discount_type',
        'discount_value',
        'min_quantity',
        'start_date',
        'end_date',
        'is_active',
        'category_id',
        'creator_id',
        'created_by'
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'min_quantity' => 'integer',
            'is_active' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date'
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(ProductServiceItem::class, 'pos_discount_products', 'pos_discount_id', 'product_id')
            ->withTimestamps();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductServiceCategory::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeForCreator($query)
    {
        return $query->where('created_by', creatorId());
    }
}
