<?php

namespace Automas\LandingPage\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNewsletterSubscriberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email|max:255|unique:newsletter_subscribers,email',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('Email address is required.'),
            'email.email' => __('Please enter a valid email address.'),
            'email.unique' => __('This email is already subscribed to our newsletter.'),
        ];
    }
}