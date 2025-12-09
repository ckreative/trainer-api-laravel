<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAvailabilityScheduleRequest extends FormRequest
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
            'name' => ['required', 'string', 'min:1', 'max:100'],
            'isDefault' => ['sometimes', 'boolean'],
            'timezone' => ['required', 'string', 'timezone:all'],
            'schedule' => ['required', 'array', 'min:7', 'max:7'],
            'schedule.*.day' => ['required', 'string', 'in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday'],
            'schedule.*.enabled' => ['required', 'boolean'],
            'schedule.*.slots' => ['present', 'array'],
            'schedule.*.slots.*.start' => ['required', 'string', 'regex:/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/'],
            'schedule.*.slots.*.end' => ['required', 'string', 'regex:/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/'],
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
            'name.required' => 'Schedule name is required',
            'name.min' => 'Schedule name must be at least 1 character',
            'name.max' => 'Schedule name cannot exceed 100 characters',
            'timezone.required' => 'Timezone is required',
            'timezone.timezone' => 'Invalid timezone identifier',
            'schedule.required' => 'Schedule array is required',
            'schedule.min' => 'Schedule must contain exactly 7 days',
            'schedule.max' => 'Schedule must contain exactly 7 days',
            'schedule.*.day.required' => 'Day is required for each schedule entry',
            'schedule.*.day.in' => 'Invalid day value',
            'schedule.*.enabled.required' => 'Enabled flag is required for each day',
            'schedule.*.enabled.boolean' => 'Enabled must be a boolean value',
            'schedule.*.slots.required' => 'Slots array is required for each day',
            'schedule.*.slots.*.start.required' => 'Start time is required for each slot',
            'schedule.*.slots.*.start.regex' => 'Start time must be in HH:MM format (24-hour)',
            'schedule.*.slots.*.end.required' => 'End time is required for each slot',
            'schedule.*.slots.*.end.regex' => 'End time must be in HH:MM format (24-hour)',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default value for isDefault if not provided
        if (!$this->has('isDefault')) {
            $this->merge([
                'isDefault' => false,
            ]);
        }
    }
}
