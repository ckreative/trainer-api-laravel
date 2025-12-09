<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBrandingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'handle' => [
                'sometimes',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-z0-9][a-z0-9-]*[a-z0-9]$/',
                Rule::unique('users', 'handle')->ignore($userId),
            ],
            'brandName' => 'sometimes|string|max:100',
            'primaryColor' => ['sometimes', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'heroImageUrl' => 'nullable|url|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'handle.regex' => 'Handle must be lowercase letters, numbers, and hyphens only. Cannot start or end with a hyphen.',
            'handle.unique' => 'This handle is already taken.',
            'primaryColor.regex' => 'Primary color must be a valid hex color (e.g., #D6FF00).',
        ];
    }
}
