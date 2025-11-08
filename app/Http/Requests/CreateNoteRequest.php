<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateNoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Anonymous note creation is allowed for everyone
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'content' => [
                'required',
                'string',
                'max:1048576', // 1MB in bytes
            ],
            'title' => [
                'nullable',
                'string',
                'max:255',
            ],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'max:255',
                'confirmed',
            ],
            'password_confirmation' => [
                'required_with:password',
            ],
            'expires_at' => [
                'nullable',
                'date',
                'after:now',
            ],
            'view_limit' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Check if "Never" expiration is being requested (null expires_at)
            // and user is not authenticated
            if (
                $this->has('expires_at') &&
                $this->input('expires_at') === null &&
                ! auth()->check()
            ) {
                $validator->errors()->add(
                    'expires_at',
                    'The "Never" expiration option is only available to authenticated users.'
                );
            }
        });
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'content.required' => 'Please enter some content for your note.',
            'content.max' => 'Content is too large. Maximum size is 1MB.',
            'title.max' => 'Title cannot exceed 255 characters.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.max' => 'Password cannot exceed 255 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password_confirmation.required_with' => 'Please confirm your password.',
            'expires_at.date' => 'Please enter a valid expiration date.',
            'expires_at.after' => 'Expiration date must be in the future.',
            'view_limit.min' => 'View limit must be at least 1.',
            'view_limit.max' => 'View limit cannot exceed 100.',
            'view_limit.integer' => 'View limit must be a whole number.',
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
            'content' => 'note content',
            'title' => 'title',
            'password' => 'password',
            'password_confirmation' => 'password confirmation',
            'expires_at' => 'expiration time',
            'view_limit' => 'view limit',
        ];
    }
}
