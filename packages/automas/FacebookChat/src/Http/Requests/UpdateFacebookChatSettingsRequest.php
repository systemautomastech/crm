<?php

namespace Automas\FacebookChat\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFacebookChatSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'settings.facebook_client_id' => 'required|string',
            'settings.facebook_access_token' => 'required|string',
            'settings.facebookchat_enabled' => 'string|in:on,off',
        ];
    }

    public function messages(): array
    {
        return [
            'settings.facebook_client_id.required' => __('Facebook Client ID is required.'),
            'settings.facebook_access_token.required' => __('Facebook Access Token is required.'),
            'settings.facebookchat_enabled.in' => __('Facebook Chat enabled must be either on or off.'),
        ];
    }
}