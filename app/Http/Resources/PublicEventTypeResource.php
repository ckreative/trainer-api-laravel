<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicEventTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * Only exposes public information about event types for booking.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Use event type's schedule, or fall back to default from additional data
        $schedule = $this->availabilitySchedule;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'duration' => $this->duration,
            'location' => $this->location,
            'availability' => $schedule ? [
                'timezone' => $schedule->timezone,
                'schedule' => $schedule->schedule,
            ] : null,
        ];
    }
}
