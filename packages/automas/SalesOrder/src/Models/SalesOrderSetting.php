<?php

namespace Automas\SalesOrder\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Key-value settings store for the SalesOrder module.
 * One row per (creator_id, option). Follows SalesInvoiceSetup / QuotationSetting pattern.
 */
class SalesOrderSetting extends Model
{
    protected $table = 'sales_order_settings';

    protected $fillable = [
        'option',
        'value',
        'creator_id',
        'created_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Static Helpers ──────────────────────────────────────────────────────

    public static function getSettings($creatorId = null): array
    {
        $creatorId = $creatorId ?? (function_exists('creatorId') ? creatorId() : auth()->id());
        $authId    = auth()->id();

        $defaults = [
            'so_prefix'          => 'SO',
            'so_starting_number' => '1',
            'dc_prefix'          => 'DC',
            'dc_starting_number' => '1',
            'default_notes'      => '',
            'default_terms'      => '',
            'footer_note'        => '',
            'template_color'     => '#3B82F6',
            'show_logo'          => 'on',
            'logo_image'         => '',
            'enable_letterhead'  => 'off',
            'bg_letterhead'      => '',
        ];

        $targetIds = array_filter(array_unique([$creatorId, $authId]));
        if (empty($targetIds)) {
            return $defaults;
        }

        $dbSettings = static::whereIn('creator_id', $targetIds)
            ->pluck('value', 'option')
            ->toArray();

        return array_merge($defaults, $dbSettings);
    }

    public static function setSettings(array $settings, $creatorId = null): void
    {
        $creatorId = $creatorId ?? (function_exists('creatorId') ? creatorId() : auth()->id());

        if (!$creatorId) {
            return;
        }

        foreach ($settings as $option => $value) {
            static::updateOrCreate(
                [
                    'creator_id' => $creatorId,
                    'option'     => $option,
                ],
                [
                    'value'      => is_array($value) ? json_encode($value) : (string) ($value ?? ''),
                    'created_by' => $creatorId,
                ]
            );
        }
    }
}
