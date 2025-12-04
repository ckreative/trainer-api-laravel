<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'eventTypeId' => $this->event_type_id,
            'title' => $this->title,
            'startTime' => $this->start_time->toIso8601String(),
            'endTime' => $this->end_time->toIso8601String(),
            'status' => $this->status,
            'attendeeName' => $this->attendee_name,
            'attendeeEmail' => $this->attendee_email,
            'location' => $this->location,
            'meetingUrl' => $this->meeting_url,
            'notes' => $this->notes,
            'timezone' => $this->timezone,
            'isRecurring' => $this->is_recurring,
            'recurrenceRule' => $this->recurrence_rule,
            'createdAt' => $this->created_at->toIso8601String(),
            'updatedAt' => $this->updated_at->toIso8601String(),
            'eventType' => new EventTypeResource($this->whenLoaded('eventType')),
        ];
    }
}
