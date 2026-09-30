<?php

namespace Automas\Pbx\Services;

class WebRtcConfigService
{
    public function __construct(
        protected PbxContextService $context
    ) {
    }

    public function getConfigForUser(?int $userId = null, ?int $creatorId = null): ?array
    {
        // if (!$this->context->canUseSoftphone($userId, $creatorId)) {
        //     return null;
        // }

        $setting = $this->context->getCreatorSetting($creatorId);
        $extension = $this->context->getUserExtension($userId, $creatorId);
        
        if (!$setting || !$extension) {
            return null;
        }

        return [
            'extension' => $extension->extension,
            'sip_secret' => $extension->sip_secret,
            'caller_id' => $extension->caller_id ?? $extension->extension,
            'sip_domain' => $setting->sip_domain,
            'websocket_url' => $setting->websocket_url,
            'stun_server' => $setting->stun_server ?? 'stun:stun.l.google.com:19302',
        ];
    }
}
