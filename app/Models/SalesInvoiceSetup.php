<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesInvoiceSetup extends Model
{
    protected $table = 'sales_invoice_setups';

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
            'sales_invoice_logo' => '',
            'sales_invoice_show_logo' => 'on',
            'sales_invoice_bg_letterhead' => '',
            'sales_invoice_enable_letterhead' => 'off',
            'sales_invoice_default_payment_terms' => '<p>Payment due within 30 days of invoice date.</p>',
            'sales_invoice_prefix' => 'SI',
            'sales_invoice_starting_number' => '01',
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
