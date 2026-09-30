<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseInvoiceSetup extends Model
{
    protected $table = 'purchase_invoice_setups';

    protected $fillable = [
        'option',
        'value',
        'creator_id',
    ];

    public static function getSettings($creatorId = null)
    {
        $creatorId = $creatorId ?? creatorId();
        $authId = auth()->id();

        $defaults = [
            'purchase_invoice_logo' => '',
            'purchase_invoice_show_logo' => 'on',
            'purchase_invoice_bg_letterhead' => '',
            'purchase_invoice_enable_letterhead' => 'off',
            'purchase_invoice_default_payment_terms' => '<p>Payment due within 30 days of invoice date.</p>',
            'purchase_invoice_prefix' => 'PI',
            'purchase_invoice_starting_number' => '01',
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

    public static function setSettings(array $settings, $creatorId = null)
    {
        $creatorId = $creatorId ?? creatorId();

        if (!$creatorId) {
            return;
        }

        foreach ($settings as $option => $value) {
            static::updateOrCreate(
                [
                    'creator_id' => $creatorId,
                    'option' => $option,
                ],
                [
                    'value' => is_array($value) ? json_encode($value) : (string) ($value ?? ''),
                ]
            );
        }
    }
}
