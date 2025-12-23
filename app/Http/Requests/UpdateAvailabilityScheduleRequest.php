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
            // Date overrides validation
            'dateOverrides' => ['sometimes', 'array'],
            'dateOverrides.*.date' => ['required_with:dateOverrides', 'string', 'date_format:Y-m-d'],
            'dateOverrides.*.type' => ['required_with:dateOverrides', 'string', 'in:available,unavailable'],
            'dateOverrides.*.slots' => ['required_if:dateOverrides.*.type,available', 'array'],
            'dateOverrides.*.slots.*.start' => ['required_with:dateOverrides.*.slots', 'string', 'regex:/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/'],
            'dateOverrides.*.slots.*.end' => ['required_with:dateOverrides.*.slots', 'string', 'regex:/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/'],
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
            // Date overrides messages
            'dateOverrides.*.date.required_with' => 'Date is required for each override',
            'dateOverrides.*.date.date_format' => 'Date must be in YYYY-MM-DD format',
            'dateOverrides.*.type.required_with' => 'Override type is required',
            'dateOverrides.*.type.in' => 'Override type must be either available or unavailable',
            'dateOverrides.*.slots.required_if' => 'Time slots are required when override is set to available',
            'dateOverrides.*.slots.*.start.required_with' => 'Start time is required for each slot',
            'dateOverrides.*.slots.*.start.regex' => 'Start time must be in HH:MM format (24-hour)',
            'dateOverrides.*.slots.*.end.required_with' => 'End time is required for each slot',
            'dateOverrides.*.slots.*.end.regex' => 'End time must be in HH:MM format (24-hour)',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate schedule slots
            $schedule = $this->input('schedule', []);
            foreach ($schedule as $dayIndex => $day) {
                if (!empty($day['slots'])) {
                    foreach ($day['slots'] as $slotIndex => $slot) {
                        if (isset($slot['start'], $slot['end']) && $slot['start'] >= $slot['end']) {
                            $validator->errors()->add(
                                "schedule.{$dayIndex}.slots.{$slotIndex}",
                                "End time must be after start time for {$day['day']}"
                            );
                        }
                    }
                }
            }

            // Validate dateOverrides slots
            $dateOverrides = $this->input('dateOverrides', []);
            foreach ($dateOverrides as $overrideIndex => $override) {
                if (!empty($override['slots'])) {
                    foreach ($override['slots'] as $slotIndex => $slot) {
                        if (isset($slot['start'], $slot['end']) && $slot['start'] >= $slot['end']) {
                            $validator->errors()->add(
                                "dateOverrides.{$overrideIndex}.slots.{$slotIndex}",
                                "End time must be after start time for date override on {$override['date']}"
                            );
                        }
                    }
                }
            }
        });
    }
}
