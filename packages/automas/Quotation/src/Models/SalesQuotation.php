<?php

namespace Automas\Quotation\Models;

use App\Models\SalesProposal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Models\Warehouse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Automas\Account\Models\Customer;
use Illuminate\Support\Facades\DB;

class SalesQuotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_number',
        'subject',
        'revision_number',
        'parent_quotation_id',
        'proposal_id',
        'quotation_date',
        'due_date',
        'customer_type',
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'warehouse_id',
        'is_recurring',
        'is_prepaid',
        'is_tax_enabled',
        'otc_discount_type',
        'otc_discount_value',
        'mrc_discount_type',
        'mrc_discount_value',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'status',
        'converted_to_invoice',
        'invoice_id',
        'sales_order_id',
        'payment_terms',
        'notes',
        'creator_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quotation_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'is_recurring' => 'boolean',
            'is_prepaid' => 'boolean',
            'is_tax_enabled' => 'boolean',
            'converted_to_invoice' => 'boolean',
            'sales_order_id'       => 'integer',
        ];
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(SalesProposal::class, 'parent_quotation_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesQuotationItem::class, 'quotation_id');
    }

    public function contents(): HasMany
    {
        return $this->hasMany(SalesQuotationContent::class, 'quotation_id')->orderBy('sort_order');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function customerDetails(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'user_id');
    }

    public function parentQuotation(): BelongsTo
    {
        return $this->belongsTo(SalesQuotation::class, 'parent_quotation_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(SalesQuotation::class, 'parent_quotation_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quotation) {
            if (empty($quotation->quotation_number)) {
                $quotation->quotation_number = static::generateQuotationNumber();
            }
        });
    }

    public static function generateQuotationNumber($date = null): string
    {
        $creatorId = creatorId();
        $dateFormatted = $date ? date('Y-m-d', strtotime($date)) : date('Y-m-d');

        return DB::transaction(function () use ($creatorId, $dateFormatted) {
            $settings = QuotationSetting::getSettings($creatorId);

            $prefix = $settings['quotation_prefix'] ?? 'QT-';
            $startingNumber = (int) ($settings['quotation_starting_number'] ?? 1001);

            $nextNumber = $startingNumber + 1;
            QuotationSetting::setSettings([
                'quotation_starting_number' => (string) $nextNumber,
            ], $creatorId);

            return "{$prefix}{$startingNumber}-{$dateFormatted}";
        });
    }
    public static function GivePermissionToRoles($role_id = null, $rolename = null)
    {
        $client_permission = [
            'manage-quotations',
            'manage-own-quotations',
            'view-quotations',
            'print-quotations',
            'approve-quotations',
            'reject-quotations'
        ];

        if ($rolename == 'client') {
            $roles_v = Role::where('name', 'client')->where('id', $role_id)->first();
            foreach ($client_permission as $permission_v) {
                $permission = Permission::where('name', $permission_v)->first();
                if (!empty($permission)) {
                    if (!$roles_v->hasPermissionTo($permission_v)) {
                        $roles_v->givePermissionTo($permission);
                    }
                }
            }
        }
    }

    /**
     * Loose reference back to the Sales Order this quotation was converted into.
     * No FK constraint enforced — SalesOrder addon may not always be active.
     */
    public function salesOrder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        if (class_exists(\Automas\SalesOrder\Models\SalesOrder::class)) {
            return $this->belongsTo(\Automas\SalesOrder\Models\SalesOrder::class, 'sales_order_id');
        }
        // Return a stub BelongsTo that will yield null
        return $this->belongsTo(static::class, 'sales_order_id');
    }
}