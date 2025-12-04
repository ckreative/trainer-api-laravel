<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBookingRequest extends FormRequest
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
            'eventTypeId' => 'required|uuid|exists:event_types,id',
            'title' => 'required|string|max:255',
            'startTime' => 'required|date',
            'endTime' => 'required|date|after:startTime',
            'status' => 'sometimes|in:upcoming,unconfirmed,recurring,past,cancelled',
            'attendeeName' => 'required|string|max:255',
            'attendeeEmail' => 'required|email|max:255',
            'location' => 'nullable|string|max:255',
            'meetingUrl' => 'nullable|url|max:500',
            'notes' => 'nullable|string|max:2000',
            'timezone' => 'sometimes|string|max:100',
            'isRecurring' => 'sometimes|boolean',
            'recurrenceRule' => 'nullable|string|max:500',
        ];
    }
}
