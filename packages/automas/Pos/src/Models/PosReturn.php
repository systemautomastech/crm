<?php

namespace Automas\Pos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Warehouse;

class PosReturn extends Model
{
    protected $fillable = [
        'return_number',
        'return_date',
        'customer_id',
        'warehouse_id',
        'original_pos_id',
        'reason',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'status',
        'notes',
        'creator_id',
        'created_by'
    ];

    protected $casts = [
        'return_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2'
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PosReturnItem::class, 'return_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function customerDetails(): BelongsTo
    {
        return $this->belongsTo(\Automas\Account\Models\Customer::class, 'customer_id', 'user_id');
    }

    public function originalPos(): BelongsTo
    {
        return $this->belongsTo(Pos::class, 'original_pos_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($return) {
            if (empty($return->return_number)) {
                $return->return_number = static::generateReturnNumber($return->created_by);
            }
        });
    }

    public static function generateReturnNumber($createdBy = null): string
    {
        $createdBy = $createdBy ?? (auth()->check() ? creatorId() : null);
        
        $year = date('Y');
        $month = date('m');
        
        $query = static::where('return_number', 'like', "PR-{$year}-{$month}-%");
        
        if ($createdBy) {
            $query->where('created_by', $createdBy);
        }
        
        $lastReturn = $query->orderBy('return_number', 'desc')->first();

        if ($lastReturn) {
            $lastNumber = (int) substr($lastReturn->return_number, -3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return "PR-{$year}-{$month}-" . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
