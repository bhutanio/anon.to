<?php

namespace App\Http\Requests\Admin;

use App\Models\AllowList;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAllowListRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', AllowList::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'domain' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['allow', 'block'])],
            'pattern_type' => ['required', Rule::in(['exact', 'wildcard', 'regex'])],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'domain.required' => 'The domain field is required.',
            'domain.max' => 'The domain must not exceed 255 characters.',
            'type.required' => 'The type field is required.',
            'type.in' => 'The type must be either allow or block.',
            'pattern_type.required' => 'The pattern type field is required.',
            'pattern_type.in' => 'The pattern type must be exact, wildcard, or regex.',
            'reason.max' => 'The reason must not exceed 500 characters.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('pattern_type') && $this->pattern_type === 'regex' && $this->has('domain')) {
            $isValid = @preg_match($this->domain, '') !== false;

            if (! $isValid) {
                $this->merge([
                    'invalid_regex' => true,
                ]);
            }
        }
    }

    /**
     * Add validation rules after the standard ones.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->has('invalid_regex') && $this->invalid_regex) {
                $validator->errors()->add('domain', 'The regex pattern is invalid.');
            }
        });
    }
}
