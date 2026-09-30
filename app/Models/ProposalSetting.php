<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProposalSetting extends Model
{
    protected $fillable = [
        'option',
        'value',
        'creator_id',
        'created_by',
    ];

    public function company()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public static function getSettings($creatorId = null)
    {
        $creatorId = $creatorId ?? (function_exists('creatorId') && auth()->check() ? creatorId() : auth()->id());
        $authId = auth()->id();

        $defaults = [
            'proposal_prefix' => 'PRO',
            'proposal_starting_number' => '1',
            'default_validity_days' => '30',
            'logo_image' => '',
            'header_logo_align' => 'right',
            'background_image' => '',
            'template_color' => '#E9591C',
            'default_terms' => '<h2>Terms & Conditions</h2><p>1. Proposal is valid for 30 days from issuance.<br/>2. Payment terms: 50% deposit upon acceptance, 50% on project completion.</p>',
        ];

        $targetIds = array_filter(array_unique([$creatorId, $authId]));
        if (empty($targetIds)) {
            return $defaults;
        }

        $dbSettings = static::where(function ($q) use ($targetIds) {
            $q->whereIn('creator_id', $targetIds)
                ->orWhereIn('created_by', $targetIds);
        })
            ->pluck('value', 'option')
            ->toArray();

        return array_merge($defaults, $dbSettings);
    }


    public static function setSettings(array $settings, $creatorId = null)
    {
        $creatorId = $creatorId ?? (function_exists('creatorId') ? creatorId() : auth()->id());

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
                    'created_by' => creatorId(),
                ]
            );
        }
    }
}
