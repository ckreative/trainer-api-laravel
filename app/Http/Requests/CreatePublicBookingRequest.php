<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePublicBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Public bookings are authorized via app key middleware.
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
            'eventTypeId' => 'required|uuid|exists:event_types,id',
            'startTime' => 'required|date|after:now',
            'attendeeName' => 'required|string|max:255',
            'attendeeEmail' => 'required|email|max:255',
            'attendeePhone' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:2000',
            'timezone' => 'sometimes|string|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'eventTypeId.required' => 'Please select a session type.',
            'eventTypeId.exists' => 'The selected session type is not available.',
            'startTime.required' => 'Please select a date and time.',
            'startTime.after' => 'The booking time must be in the future.',
            'attendeeName.required' => 'Please enter your full name.',
            'attendeeEmail.required' => 'Please enter your email address.',
            'attendeeEmail.email' => 'Please enter a valid email address.',
        ];
    }
}
