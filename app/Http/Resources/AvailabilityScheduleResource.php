<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AvailabilityScheduleResource extends JsonResource
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
            'name' => $this->name,
            'isDefault' => $this->is_default,
            'timezone' => $this->timezone,
            'schedule' => $this->schedule,
            'dateOverrides' => $this->date_overrides ?? [],
            'eventTypeCount' => $this->whenLoaded('eventTypes', function () {
                return $this->eventTypes->count();
            }, 0),
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}
