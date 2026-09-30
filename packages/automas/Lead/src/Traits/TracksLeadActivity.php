<?php

namespace Automas\Lead\Traits;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Automas\Lead\Models\LeadActivityLog;
use Automas\Lead\Models\LeadStage;
use Automas\Lead\Models\Pipeline;
use Automas\Lead\Models\Deal;
use Automas\Lead\Models\Label;
use Automas\Lead\Models\Source;
use Automas\ProductService\Models\ProductServiceItem;

trait TracksLeadActivity
{
    /**
     * Boot the trait to listen for Eloquent model events.
     */
    public static function bootTracksLeadActivity(): void
    {
        static::created(function ($lead) {
            $user = Auth::user() ?? User::find($lead->creator_id) ?? User::find($lead->created_by);
            $userId = $user?->id;
            if (!$userId) {
                return;
            }

            LeadActivityLog::create([
                'user_id'  => $userId,
                'lead_id'  => $lead->id,
                'log_type' => 'Create Lead',
                'remark'   => json_encode([
                    'title'     => __('Created lead') . ': ' . $lead->name,
                    'user_name' => $user->name,
                ]),
            ]);
        });

        static::updating(function ($lead) {
            $user = Auth::user() ?? User::find($lead->creator_id) ?? User::find($lead->created_by);
            $userId = $user?->id;
            if (!$userId) {
                return;
            }

            $dirty = $lead->getDirty();

            // Filter out internal fields and fields that have no actual value change
            $actualChanged = [];
            foreach ($dirty as $field => $newValue) {
                if (in_array($field, ['updated_at', 'created_at', 'order', 'id'])) {
                    continue;
                }

                $originalValue = $lead->getOriginal($field);

                // Date comparison
                if ($field === 'date') {
                    $origTime = $originalValue ? strtotime((string)$originalValue) : null;
                    $newTime = $newValue ? strtotime((string)$newValue) : null;
                    if ($origTime === $newTime) {
                        continue;
                    }
                }
                // Boolean comparison
                elseif ($field === 'is_active') {
                    if ((bool)$originalValue === (bool)$newValue) {
                        continue;
                    }
                }
                // Numeric / ID comparison
                elseif (in_array($field, ['user_id', 'pipeline_id', 'stage_id', 'creator_id', 'created_by', 'is_converted', 'lead_import_id'])) {
                    $origId = ($originalValue === null || $originalValue === '') ? null : (int)$originalValue;
                    $newId = ($newValue === null || $newValue === '') ? null : (int)$newValue;
                    if ($origId === $newId) {
                        continue;
                    }
                }
                // String / text comparison
                else {
                    $normOrig = ($originalValue === null || $originalValue === '') ? '' : trim((string)$originalValue);
                    $normNew = ($newValue === null || $newValue === '') ? '' : trim((string)$newValue);
                    if ($normOrig === $normNew) {
                        continue;
                    }
                }

                $actualChanged[$field] = [
                    'old' => $originalValue,
                    'new' => $newValue,
                ];
            }

            if (empty($actualChanged)) {
                return;
            }

            // 1. Stage change / Movement
            if (isset($actualChanged['stage_id'])) {
                $oldStageId = $actualChanged['stage_id']['old'];
                $newStageId = $actualChanged['stage_id']['new'];

                if ($oldStageId != $newStageId) {
                    $oldStage = $oldStageId ? LeadStage::find($oldStageId) : null;
                    $newStage = $newStageId ? LeadStage::find($newStageId) : null;

                    LeadActivityLog::create([
                        'user_id'  => $userId,
                        'lead_id'  => $lead->id,
                        'log_type' => 'Move',
                        'remark'   => json_encode([
                            'title'      => $lead->name,
                            'old_status' => $oldStage ? $oldStage->name : '',
                            'new_status' => $newStage ? $newStage->name : '',
                            'user_name'  => $user->name,
                        ]),
                    ]);
                }

                unset($actualChanged['stage_id']);
            }

            // 2. Converted to Deal
            if (isset($actualChanged['is_converted']) && !empty($actualChanged['is_converted']['new'])) {
                $deal = Deal::find($actualChanged['is_converted']['new']);
                LeadActivityLog::create([
                    'user_id'  => $userId,
                    'lead_id'  => $lead->id,
                    'log_type' => 'Convert to Deal',
                    'remark'   => json_encode([
                        'title'     => __('Converted lead to deal') . ($deal ? ': ' . $deal->name : ''),
                        'user_name' => $user->name,
                    ]),
                ]);

                unset($actualChanged['is_converted']);
            }

            // 3. Notes update
            if (count($actualChanged) === 1 && isset($actualChanged['notes'])) {
                LeadActivityLog::create([
                    'user_id'  => $userId,
                    'lead_id'  => $lead->id,
                    'log_type' => 'Update Notes',
                    'remark'   => json_encode([
                        'title'     => __('Updated lead notes'),
                        'user_name' => $user->name,
                    ]),
                ]);
                return;
            }

            // 4. Labels update
            if (count($actualChanged) === 1 && isset($actualChanged['labels'])) {
                $oldLabels = static::formatLabelsText($actualChanged['labels']['old']);
                $newLabels = static::formatLabelsText($actualChanged['labels']['new']);

                LeadActivityLog::create([
                    'user_id'  => $userId,
                    'lead_id'  => $lead->id,
                    'log_type' => 'Update Labels',
                    'remark'   => json_encode([
                        'title'     => __('Updated lead labels'),
                        'user_name' => $user->name,
                        'changes'   => [
                            [
                                'field' => __('Labels'),
                                'old'   => $oldLabels ?: '-',
                                'new'   => $newLabels ?: '-',
                            ]
                        ],
                    ]),
                ]);
                return;
            }

            // 5. General field changes
            if (!empty($actualChanged)) {
                $fieldLabels = [
                    'name'        => __('Name'),
                    'email'       => __('Email'),
                    'subject'     => __('Subject'),
                    'phone'       => __('Phone'),
                    'user_id'     => __('Owner/Assignee'),
                    'date'        => __('Follow Up Date'),
                    'pipeline_id' => __('Pipeline'),
                    'notes'       => __('Notes'),
                    'labels'      => __('Labels'),
                    'sources'     => __('Sources'),
                    'products'    => __('Products'),
                    'is_active'   => __('Status'),
                ];

                $changedFieldsList = [];
                $detailedChanges = [];

                foreach ($actualChanged as $field => $change) {
                    $fieldName = $fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field));
                    $changedFieldsList[] = $fieldName;

                    $oldDisplay = static::formatFieldValue($field, $change['old']);
                    $newDisplay = static::formatFieldValue($field, $change['new']);

                    $detailedChanges[] = [
                        'field' => $fieldName,
                        'old'   => $oldDisplay ?: '-',
                        'new'   => $newDisplay ?: '-',
                    ];
                }

                LeadActivityLog::create([
                    'user_id'  => $userId,
                    'lead_id'  => $lead->id,
                    'log_type' => 'Update Lead',
                    'remark'   => json_encode([
                        'title'     => __('Updated lead details') . ' (' . implode(', ', $changedFieldsList) . ')',
                        'user_name' => $user->name,
                        'changes'   => $detailedChanges,
                    ]),
                ]);
            }
        });
    }

    /**
     * Helper to humanize field values in activity logs.
     */
    protected static function formatFieldValue(string $field, mixed $val): string
    {
        if ($val === null || $val === '') {
            return '';
        }

        if ($field === 'user_id') {
            return User::find($val)?->name ?? (string)$val;
        }

        if ($field === 'pipeline_id') {
            return Pipeline::find($val)?->name ?? (string)$val;
        }

        if ($field === 'stage_id') {
            return LeadStage::find($val)?->name ?? (string)$val;
        }

        if ($field === 'is_active') {
            return $val ? __('Active') : __('Inactive');
        }

        if ($field === 'labels') {
            return static::formatLabelsText($val);
        }

        if ($field === 'sources') {
            return static::formatSourcesText($val);
        }

        if ($field === 'products' && module_is_active('ProductService')) {
            return static::formatProductsText($val);
        }

        return (string)$val;
    }

    protected static function formatLabelsText(mixed $val): string
    {
        if (!$val) return '';
        $ids = is_array($val) ? $val : explode(',', (string)$val);
        return Label::whereIn('id', array_filter($ids))->pluck('name')->implode(', ');
    }

    protected static function formatSourcesText(mixed $val): string
    {
        if (!$val) return '';
        $ids = is_array($val) ? $val : explode(',', (string)$val);
        return Source::whereIn('id', array_filter($ids))->pluck('name')->implode(', ');
    }

    protected static function formatProductsText(mixed $val): string
    {
        if (!$val || !module_is_active('ProductService')) return '';
        $ids = is_array($val) ? $val : explode(',', (string)$val);
        return ProductServiceItem::whereIn('id', array_filter($ids))->pluck('name')->implode(', ');
    }
}
