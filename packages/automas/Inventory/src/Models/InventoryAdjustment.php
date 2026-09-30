<?php

namespace Automas\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Warehouse;
use App\Models\User;

class InventoryAdjustment extends Model
{
    protected $fillable = [
        'adjustment_number',
        'adjustment_date',
        'warehouse_id',
        'adjustment_type',
        'reason',
        'total_value',
        'status',
        'approved_by',
        'approved_at',
        'creator_id',
        'created_by',
    ];

    protected $casts = [
        'adjustment_date' => 'date',
        'total_value' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryAdjustmentItem::class, 'adjustment_id');
    }

    public static function generateAdjustmentNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $lastAdjustment = static::where('adjustment_number', 'like', "ADJ-{$year}-{$month}-%")
            ->where('created_by', creatorId())
            ->orderBy('adjustment_number', 'desc')
            ->first();

        if ($lastAdjustment) {
            $lastNumber = (int) substr($lastAdjustment->adjustment_number, -3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return "ADJ-{$year}-{$month}-" . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($adjustment) {
            if (empty($adjustment->adjustment_number)) {
                $adjustment->adjustment_number = static::generateAdjustmentNumber();
            }
        });
    }
}
