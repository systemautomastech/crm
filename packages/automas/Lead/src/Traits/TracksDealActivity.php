<?php

namespace Automas\Lead\Traits;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Automas\Lead\Models\DealActivityLog;
use Automas\Lead\Models\DealStage;
use Automas\Lead\Models\Pipeline;
use Automas\Lead\Models\Label;
use Automas\Lead\Models\Source;
use Automas\ProductService\Models\ProductServiceItem;

trait TracksDealActivity
{
    /**
     * Boot the trait to listen for Eloquent model events.
     */
    public static function bootTracksDealActivity(): void
    {
        static::created(function ($deal) {
            $user = Auth::user() ?? User::find($deal->creator_id) ?? User::find($deal->created_by);
            $userId = $user?->id;
            if (!$userId) {
                return;
            }

            DealActivityLog::create([
                'user_id'  => $userId,
                'deal_id'  => $deal->id,
                'log_type' => 'Create Deal',
                'remark'   => json_encode([
                    'title'     => __('Created deal') . ': ' . $deal->name,
                    'user_name' => $user->name,
                ]),
            ]);
        });

        static::updating(function ($deal) {
            $user = Auth::user() ?? User::find($deal->creator_id) ?? User::find($deal->created_by);
            $userId = $user?->id;
            if (!$userId) {
                return;
            }

            $dirty = $deal->getDirty();

            // Filter out internal fields and fields that have no actual value change
            $actualChanged = [];
            foreach ($dirty as $field => $newValue) {
                if (in_array($field, ['updated_at', 'created_at', 'order', 'id'])) {
                    continue;
                }

                $originalValue = $deal->getOriginal($field);

                // Price / Decimal comparison
                if ($field === 'price') {
                    $origPrice = (float)($originalValue ?? 0);
                    $newPrice = (float)($newValue ?? 0);
                    if (abs($origPrice - $newPrice) < 0.0001) {
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
                elseif (in_array($field, ['pipeline_id', 'stage_id', 'creator_id', 'created_by'])) {
                    $origId = ($originalValue === null || $originalValue === '') ? null : (int)$originalValue;
                    $newId = ($newValue === null || $newValue === '') ? null : (int)$newValue;
                    if ($origId === $newId) {
                        continue;
                    }
                }
                // Array fields (e.g. sources, products, labels if serialized or json)
                elseif (in_array($field, ['sources', 'products', 'labels'])) {
                    $origClean = static::normalizeArrayField($originalValue);
                    $newClean = static::normalizeArrayField($newValue);
                    sort($origClean);
                    sort($newClean);

                    if ($origClean === $newClean) {
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
                    $oldStage = $oldStageId ? DealStage::find($oldStageId) : null;
                    $newStage = $newStageId ? DealStage::find($newStageId) : null;

                    DealActivityLog::create([
                        'user_id'  => $userId,
                        'deal_id'  => $deal->id,
                        'log_type' => 'Move',
                        'remark'   => json_encode([
                            'title'      => $deal->name,
                            'old_status' => $oldStage ? $oldStage->name : '',
                            'new_status' => $newStage ? $newStage->name : '',
                            'user_name'  => $user->name,
                        ]),
                    ]);
                }

                unset($actualChanged['stage_id']);
            }

            // 2. Notes update
            if (count($actualChanged) === 1 && isset($actualChanged['notes'])) {
                DealActivityLog::create([
                    'user_id'  => $userId,
                    'deal_id'  => $deal->id,
                    'log_type' => 'Update Notes',
                    'remark'   => json_encode([
                        'title'     => __('Updated deal notes'),
                        'user_name' => $user->name,
                    ]),
                ]);
                return;
            }

            // 3. Labels update
            if (count($actualChanged) === 1 && isset($actualChanged['labels'])) {
                $oldLabels = static::formatLabelsText($actualChanged['labels']['old']);
                $newLabels = static::formatLabelsText($actualChanged['labels']['new']);

                DealActivityLog::create([
                    'user_id'  => $userId,
                    'deal_id'  => $deal->id,
                    'log_type' => 'Update Labels',
                    'remark'   => json_encode([
                        'title'     => __('Updated deal labels'),
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

            // 4. General field changes
            if (!empty($actualChanged)) {
                $fieldLabels = [
                    'name'        => __('Name'),
                    'price'       => __('Price'),
                    'pipeline_id' => __('Pipeline'),
                    'phone'       => __('Phone'),
                    'status'      => __('Status'),
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

                DealActivityLog::create([
                    'user_id'  => $userId,
                    'deal_id'  => $deal->id,
                    'log_type' => 'Update Deal',
                    'remark'   => json_encode([
                        'title'     => __('Updated deal details') . ' (' . implode(', ', $changedFieldsList) . ')',
                        'user_name' => $user->name,
                        'changes'   => $detailedChanges,
                    ]),
                ]);
            }
        });
    }

    /**
     * Helper to humanize field values in deal activity logs.
     */
    protected static function formatFieldValue(string $field, mixed $val): string
    {
        if ($val === null || $val === '') {
            return '';
        }

        if ($field === 'price') {
            return is_numeric($val) ? number_format((float)$val, 2) : (string)$val;
        }

        if ($field === 'pipeline_id') {
            return Pipeline::find($val)?->name ?? (string)$val;
        }

        if ($field === 'stage_id') {
            return DealStage::find($val)?->name ?? (string)$val;
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

    /**
     * Safely normalize any value (array, json string, comma-separated string, numeric ID) into a clean string array.
     */
    protected static function normalizeArrayField(mixed $value): array
    {
        if ($value === null || $value === '' || $value === []) {
            return [];
        }

        if (is_array($value)) {
            return array_values(array_filter(array_map('strval', $value), fn($v) => trim($v) !== ''));
        }

        if (is_numeric($value)) {
            return [(string)$value];
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return [];
            }

            if (str_starts_with($trimmed, '[') || str_starts_with($trimmed, '{')) {
                $decoded = json_decode($trimmed, true);
                if (is_array($decoded)) {
                    return array_values(array_filter(array_map('strval', $decoded), fn($v) => trim($v) !== ''));
                }
            }

            $parts = explode(',', $trimmed);
            return array_values(array_filter(array_map('trim', array_map('strval', $parts)), fn($v) => $v !== ''));
        }

        return [];
    }

    protected static function formatLabelsText(mixed $val): string
    {
        $ids = static::normalizeArrayField($val);
        if (empty($ids)) {
            return '';
        }
        return Label::whereIn('id', $ids)->pluck('name')->implode(', ');
    }

    protected static function formatSourcesText(mixed $val): string
    {
        $ids = static::normalizeArrayField($val);
        if (empty($ids)) {
            return '';
        }
        return Source::whereIn('id', $ids)->pluck('name')->implode(', ');
    }

    protected static function formatProductsText(mixed $val): string
    {
        if (!module_is_active('ProductService')) {
            return '';
        }
        $ids = static::normalizeArrayField($val);
        if (empty($ids)) {
            return '';
        }
        return ProductServiceItem::whereIn('id', $ids)->pluck('name')->implode(', ');
    }
}
