<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventTypeResource extends JsonResource
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
            'title' => $this->title,
            'url' => $this->url,
            'fullUrl' => $this->full_url ?? $this->generateFullUrl(),
            'description' => $this->description,
            'duration' => $this->duration,
            'enabled' => $this->enabled,
            'allowMultipleDurations' => $this->allow_multiple_durations,
            'multipleDurationOptions' => $this->multiple_duration_options,
            'location' => $this->location,
            'customLocation' => $this->custom_location,
            'beforeEventBuffer' => $this->before_event_buffer,
            'afterEventBuffer' => $this->after_event_buffer,
            'minimumNotice' => $this->minimum_notice,
            'timeSlotInterval' => $this->time_slot_interval,
            'limitBookingFrequency' => $this->limit_booking_frequency,
            'bookingFrequencyLimit' => $this->booking_frequency_limit,
            'onlyFirstSlotPerDay' => $this->only_first_slot_per_day,
            'limitTotalDuration' => $this->limit_total_duration,
            'totalDurationLimit' => $this->total_duration_limit,
            'limitUpcomingBookings' => $this->limit_upcoming_bookings,
            'upcomingBookingsLimit' => $this->upcoming_bookings_limit,
            'limitFutureBookings' => $this->limit_future_bookings,
            'futureBookingsLimit' => $this->future_bookings_limit,
            'availabilityScheduleId' => $this->availability_schedule_id,
            'bookingCount' => $this->whenLoaded('bookings', function () {
                return $this->bookings->count();
            }, 0),
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Generate the full booking URL for this event type.
     *
     * @return string
     */
    private function generateFullUrl(): string
    {
        // Get the user's username if available
        $username = $this->user?->username ?? 'user';

        // Generate full URL based on app URL
        $baseUrl = config('app.url', 'https://yourdomain.com');

        return "{$baseUrl}/{$username}/{$this->url}";
    }
}
