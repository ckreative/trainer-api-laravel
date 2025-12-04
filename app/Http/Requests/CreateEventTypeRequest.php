<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateEventTypeRequest extends FormRequest
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
        return [
            'title' => ['required', 'string', 'min:1', 'max:100'],
            'url' => ['required', 'string', 'min:1', 'max:100', 'regex:/^[a-z0-9-]+$/'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'duration' => ['required', 'integer', 'min:5', 'max:480'],
            'location' => ['sometimes', 'nullable', 'string', 'in:Google Meet,Zoom,Microsoft Teams,Phone Call,In Person,Custom'],
            'customLocation' => ['sometimes', 'nullable', 'string', 'max:200', 'required_if:location,Custom'],
            'enabled' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Event type title is required',
            'title.min' => 'Title must be at least 1 character',
            'title.max' => 'Title cannot exceed 100 characters',
            'url.required' => 'URL slug is required',
            'url.regex' => 'URL must contain only lowercase letters, numbers, and hyphens',
            'url.min' => 'URL must be at least 1 character',
            'url.max' => 'URL cannot exceed 100 characters',
            'description.max' => 'Description cannot exceed 2000 characters',
            'duration.required' => 'Duration is required',
            'duration.min' => 'Duration must be at least 5 minutes',
            'duration.max' => 'Duration cannot exceed 480 minutes (8 hours)',
            'location.in' => 'Invalid location type',
            'customLocation.required_if' => 'Custom location details are required when location type is Custom',
            'customLocation.max' => 'Custom location cannot exceed 200 characters',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default value for enabled if not provided
        if (!$this->has('enabled')) {
            $this->merge([
                'enabled' => true,
            ]);
        }
    }
}
