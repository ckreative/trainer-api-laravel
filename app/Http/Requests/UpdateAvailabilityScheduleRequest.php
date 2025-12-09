<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAvailabilityScheduleRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'min:1', 'max:100'],
            'isDefault' => ['sometimes', 'boolean'],
            'timezone' => ['sometimes', 'string', 'timezone:all'],
            'schedule' => ['sometimes', 'array', 'min:7', 'max:7'],
            'schedule.*.day' => ['required_with:schedule', 'string', 'in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday'],
            'schedule.*.enabled' => ['required_with:schedule', 'boolean'],
            'schedule.*.slots' => ['present_with:schedule', 'array'],
            'schedule.*.slots.*.start' => ['required_with:schedule.*.slots', 'string', 'regex:/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/'],
            'schedule.*.slots.*.end' => ['required_with:schedule.*.slots', 'string', 'regex:/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/'],
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
            'name.min' => 'Schedule name must be at least 1 character',
            'name.max' => 'Schedule name cannot exceed 100 characters',
            'timezone.timezone' => 'Invalid timezone identifier',
            'schedule.min' => 'Schedule must contain exactly 7 days',
            'schedule.max' => 'Schedule must contain exactly 7 days',
            'schedule.*.day.required_with' => 'Day is required for each schedule entry',
            'schedule.*.day.in' => 'Invalid day value',
            'schedule.*.enabled.required_with' => 'Enabled flag is required for each day',
            'schedule.*.enabled.boolean' => 'Enabled must be a boolean value',
            'schedule.*.slots.required_with' => 'Slots array is required for each day',
            'schedule.*.slots.*.start.required_with' => 'Start time is required for each slot',
            'schedule.*.slots.*.start.regex' => 'Start time must be in HH:MM format (24-hour)',
            'schedule.*.slots.*.end.required_with' => 'End time is required for each slot',
            'schedule.*.slots.*.end.regex' => 'End time must be in HH:MM format (24-hour)',
        ];
    }
}
