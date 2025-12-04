<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventTypeRequest extends FormRequest
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
            // Basic fields
            'title' => ['sometimes', 'string', 'min:1', 'max:100'],
            'url' => ['sometimes', 'string', 'min:1', 'max:100', 'regex:/^[a-z0-9-]+$/'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'duration' => ['sometimes', 'integer', 'min:5', 'max:480'],
            'enabled' => ['sometimes', 'boolean'],

            // Multiple durations
            'allowMultipleDurations' => ['sometimes', 'boolean'],
            'multipleDurationOptions' => ['sometimes', 'nullable', 'array', 'required_if:allowMultipleDurations,true'],
            'multipleDurationOptions.*' => ['integer', 'min:5', 'max:480'],

            // Location
            'location' => ['sometimes', 'nullable', 'string', 'in:Google Meet,Zoom,Microsoft Teams,Phone Call,In Person,Custom'],
            'customLocation' => ['sometimes', 'nullable', 'string', 'max:200', 'required_if:location,Custom'],

            // Buffers and notice
            'beforeEventBuffer' => ['sometimes', 'integer', 'min:0', 'max:120'],
            'afterEventBuffer' => ['sometimes', 'integer', 'min:0', 'max:120'],
            'minimumNotice' => ['sometimes', 'integer', 'min:0'],
            'timeSlotInterval' => ['sometimes', 'nullable', 'integer', 'min:5', 'max:120'],

            // Booking frequency limits
            'limitBookingFrequency' => ['sometimes', 'boolean'],
            'bookingFrequencyLimit' => ['sometimes', 'nullable', 'array', 'required_if:limitBookingFrequency,true'],
            'bookingFrequencyLimit.count' => ['required_with:bookingFrequencyLimit', 'integer', 'min:1'],
            'bookingFrequencyLimit.period' => ['required_with:bookingFrequencyLimit', 'string', 'in:day,week,month'],

            // Other limits
            'onlyFirstSlotPerDay' => ['sometimes', 'boolean'],

            // Total duration limits
            'limitTotalDuration' => ['sometimes', 'boolean'],
            'totalDurationLimit' => ['sometimes', 'nullable', 'array', 'required_if:limitTotalDuration,true'],
            'totalDurationLimit.duration' => ['required_with:totalDurationLimit', 'integer', 'min:30'],
            'totalDurationLimit.period' => ['required_with:totalDurationLimit', 'string', 'in:day,week,month'],

            // Upcoming bookings limit
            'limitUpcomingBookings' => ['sometimes', 'boolean'],
            'upcomingBookingsLimit' => ['sometimes', 'nullable', 'integer', 'min:1', 'required_if:limitUpcomingBookings,true'],

            // Future bookings limit
            'limitFutureBookings' => ['sometimes', 'boolean'],
            'futureBookingsLimit' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:365', 'required_if:limitFutureBookings,true'],

            // Availability schedule
            'availabilityScheduleId' => ['sometimes', 'nullable', 'uuid', 'exists:availability_schedules,id'],
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
            'title.min' => 'Title must be at least 1 character',
            'title.max' => 'Title cannot exceed 100 characters',
            'url.regex' => 'URL must contain only lowercase letters, numbers, and hyphens',
            'url.min' => 'URL must be at least 1 character',
            'url.max' => 'URL cannot exceed 100 characters',
            'description.max' => 'Description cannot exceed 2000 characters',
            'duration.min' => 'Duration must be at least 5 minutes',
            'duration.max' => 'Duration cannot exceed 480 minutes (8 hours)',
            'location.in' => 'Invalid location type',
            'customLocation.required_if' => 'Custom location details are required when location type is Custom',
            'customLocation.max' => 'Custom location cannot exceed 200 characters',
            'multipleDurationOptions.required_if' => 'Duration options are required when multiple durations are allowed',
            'multipleDurationOptions.*.min' => 'Each duration option must be at least 5 minutes',
            'multipleDurationOptions.*.max' => 'Each duration option cannot exceed 480 minutes',
            'beforeEventBuffer.max' => 'Before event buffer cannot exceed 120 minutes',
            'afterEventBuffer.max' => 'After event buffer cannot exceed 120 minutes',
            'timeSlotInterval.min' => 'Time slot interval must be at least 5 minutes',
            'timeSlotInterval.max' => 'Time slot interval cannot exceed 120 minutes',
            'bookingFrequencyLimit.required_if' => 'Booking frequency limit settings are required when frequency limiting is enabled',
            'bookingFrequencyLimit.count.min' => 'Booking count must be at least 1',
            'bookingFrequencyLimit.period.in' => 'Period must be day, week, or month',
            'totalDurationLimit.required_if' => 'Total duration limit settings are required when duration limiting is enabled',
            'totalDurationLimit.duration.min' => 'Total duration must be at least 30 minutes',
            'totalDurationLimit.period.in' => 'Period must be day, week, or month',
            'upcomingBookingsLimit.required_if' => 'Upcoming bookings limit is required when limiting is enabled',
            'upcomingBookingsLimit.min' => 'Upcoming bookings limit must be at least 1',
            'futureBookingsLimit.required_if' => 'Future bookings limit is required when limiting is enabled',
            'futureBookingsLimit.min' => 'Future bookings limit must be at least 1 day',
            'futureBookingsLimit.max' => 'Future bookings limit cannot exceed 365 days',
            'availabilityScheduleId.exists' => 'Selected availability schedule does not exist',
        ];
    }
}
