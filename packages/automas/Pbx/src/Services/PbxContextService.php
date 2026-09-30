<?php

namespace Automas\Pbx\Services;

use Automas\Pbx\Models\PbxExtension;
use Automas\Pbx\Models\PbxSetting;

class PbxContextService
{
    public function isModuleActive(): bool
    {
        return module_is_active('Pbx');
    }

    public function getCreatorSetting(?int $creatorId = null): ?PbxSetting
    {
        $creatorId = $creatorId ?? (int) creatorId();

        if ($creatorId <= 0) {
            return null;
        }

        return PbxSetting::forCreator($creatorId)->first();
    }

    public function isPbxEnabled(?int $creatorId = null): bool
    {
        $setting = $this->getCreatorSetting($creatorId);

        return $setting && $setting->is_enabled;
    }

    public function getUserExtension(?int $userId = null, ?int $creatorId = null): ?PbxExtension
    {
        $userId = $userId ?? (int) auth()->id();
        $creatorId = $creatorId ?? (int) creatorId();

        if ($userId <= 0 || $creatorId <= 0) {
            return null;
        }

        return PbxExtension::where('user_id', $userId)
            ->active()
            ->first();
    }

    public function canUseSoftphone(?int $userId = null, ?int $creatorId = null): bool
    {
        if (!$this->isModuleActive()) {
            return false;
        }

        if (!$this->isPbxEnabled($creatorId)) {
            return false;
        }

        return $this->getUserExtension($userId, $creatorId) !== null;
    }
}
