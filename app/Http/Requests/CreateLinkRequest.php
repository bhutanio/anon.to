<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateLinkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Anonymous link creation is allowed for everyone
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $maxUrlLength = config('anon.max_url_length', 2048);

        return [
            'url' => [
                'required',
                'string',
                'url',
                "max:{$maxUrlLength}",
                'regex:/^https?:\/\//', // Must start with http:// or https://
            ],
            'expires_at' => [
                'nullable',
                'date',
                'after:now',
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'url.required' => 'Please enter a URL to shorten.',
            'url.url' => 'Please enter a valid URL.',
            'url.max' => 'The URL is too long. Maximum length is :max characters.',
            'url.regex' => 'Only HTTP and HTTPS URLs are supported.',
            'expires_at.date' => 'Please enter a valid expiration date.',
            'expires_at.after' => 'Expiration date must be in the future.',
        ];
    }

    /**
     * Get custom attribute names for error messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'url' => 'URL',
            'expires_at' => 'expiration date',
        ];
    }
}
