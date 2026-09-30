<?php

namespace Automas\WhatsAppChat\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWhatsAppChatSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'settings.whatsappchat_phone_number_id' => 'required|string',
            'settings.whatsappchat_access_token' => 'required|string',
            'settings.whatsappchat_enabled' => 'string|in:on,off',
        ];
    }

    public function messages(): array
    {
        return [
            'settings.whatsappchat_phone_number_id.required' => __('Phone Number ID is required.'),
            'settings.whatsappchat_access_token.required' => __('Access Token is required.'),
            'settings.whatsappchat_enabled.in' => __('WhatsApp Chat enabled must be either on or off.'),
        ];
    }
}