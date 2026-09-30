<?php

namespace Automas\Pbx\Rules;

use Automas\Pbx\Models\PbxExtension;
use Automas\Pbx\Models\PbxSetting;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPbxExtension implements ValidationRule
{
    public function __construct(
        protected int $creatorId,
        protected ?int $ignoreId = null
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $setting = PbxSetting::forCreator($this->creatorId)->first();

        if (!$setting) {
            $fail(__('PBX settings are not configured for this Creator.'));
            return;
        }

        if (!ctype_digit((string) $value)) {
            $fail(__('Extension must be numeric.'));
            return;
        }

        if (!$setting->isExtensionInRange((string) $value)) {
            $fail(__('Extension must be between :start and :end.', [
                'start' => $setting->extension_start,
                'end' => $setting->extension_end,
            ]));
            return;
        }

        $exists = PbxExtension::forCreator($this->creatorId)
            ->where('extension', $value)
            ->when($this->ignoreId, fn ($query) => $query->where('id', '!=', $this->ignoreId))
            ->exists();

        if ($exists) {
            $fail(__('This extension is already assigned in this Creator.'));
        }
    }
}
