<?php

namespace App\Services;

use App\Models\AvailabilitySchedule;
use App\Models\EventType;
use Carbon\Carbon;

class AvailabilityCalculator
{
    /**
     * Merge overlapping time slots into continuous blocks.
     *
     * @param array $slots Array of ['start' => 'HH:MM', 'end' => 'HH:MM']
     * @return array Merged slots
     */
    public function mergeOverlappingSlots(array $slots): array
    {
        if (empty($slots)) {
            return [];
        }

        // Sort by start time
        usort($slots, fn($a, $b) => strcmp($a['start'], $b['start']));

        $merged = [$slots[0]];

        foreach (array_slice($slots, 1) as $slot) {
            $lastIndex = count($merged) - 1;
            $last = $merged[$lastIndex];

            // If current slot starts before/when last ends, merge them
            if ($slot['start'] <= $last['end']) {
                $merged[$lastIndex]['end'] = max($last['end'], $slot['end']);
            } else {
                $merged[] = $slot;
            }
        }

        return $merged;
    }

    /**
     * Get the schedule for a specific day of the week.
     *
     * @param AvailabilitySchedule $schedule
     * @param string $dayName e.g., 'Monday', 'Tuesday'
     * @return array|null Day schedule or null if not found
     */
    public function getDaySchedule(AvailabilitySchedule $schedule, string $dayName): ?array
    {
        $scheduleData = $schedule->schedule ?? [];

        foreach ($scheduleData as $day) {
            if ($day['day'] === $dayName) {
                return $day;
            }
        }

        return null;
    }

    /**
     * Get date override for a specific date.
     *
     * @param AvailabilitySchedule $schedule
     * @param string $date Date in Y-m-d format
     * @return array|null Override data or null if none exists
     */
    public function getDateOverride(AvailabilitySchedule $schedule, string $date): ?array
    {
        $overrides = $schedule->date_overrides ?? [];

        foreach ($overrides as $override) {
            if ($override['date'] === $date) {
                return $override;
            }
        }

        return null;
    }

    /**
     * Get available time slots for a specific date.
     * Considers weekly schedule, date overrides, and merges overlapping slots.
     *
     * @param AvailabilitySchedule $schedule
     * @param Carbon $date
     * @return array Array of available time slots
     */
    public function getAvailableSlotsForDate(AvailabilitySchedule $schedule, Carbon $date): array
    {
        $dateString = $date->format('Y-m-d');
        $dayName = $date->format('l'); // Full day name (Monday, Tuesday, etc.)

        // Check for date override first
        $override = $this->getDateOverride($schedule, $dateString);

        if ($override !== null) {
            // If marked as unavailable, return empty
            if ($override['type'] === 'unavailable') {
                return [];
            }

            // Use override slots
            $slots = $override['slots'] ?? [];
            return $this->mergeOverlappingSlots($slots);
        }

        // Fall back to weekly schedule
        $daySchedule = $this->getDaySchedule($schedule, $dayName);

        if ($daySchedule === null || !($daySchedule['enabled'] ?? false)) {
            return [];
        }

        $slots = $daySchedule['slots'] ?? [];
        return $this->mergeOverlappingSlots($slots);
    }

    /**
     * Get available booking slots for an event type on a specific date.
     * Takes into account event duration, buffers, and existing bookings.
     *
     * @param EventType $eventType
     * @param Carbon $date
     * @param array $existingBookings Array of existing bookings to exclude
     * @return array Array of bookable time slots
     */
    public function getBookableSlots(EventType $eventType, Carbon $date, array $existingBookings = []): array
    {
        $schedule = $eventType->availabilitySchedule;

        if (!$schedule) {
            return [];
        }

        // Get available slots for the date
        $availableSlots = $this->getAvailableSlotsForDate($schedule, $date);

        if (empty($availableSlots)) {
            return [];
        }

        $duration = $eventType->duration;
        $beforeBuffer = $eventType->before_event_buffer ?? 0;
        $afterBuffer = $eventType->after_event_buffer ?? 0;
        $interval = $eventType->time_slot_interval ?? 15;

        // Get the schedule's timezone for proper comparison with existing bookings (stored in UTC)
        $scheduleTimezone = $schedule->timezone ?? 'UTC';

        $bookableSlots = [];

        foreach ($availableSlots as $slot) {
            $slotStart = Carbon::parse($date->format('Y-m-d') . ' ' . $slot['start'], $scheduleTimezone);
            $slotEnd = Carbon::parse($date->format('Y-m-d') . ' ' . $slot['end'], $scheduleTimezone);

            // Generate time slots within this availability window
            $currentTime = $slotStart->copy();

            while ($currentTime->copy()->addMinutes($duration)->lte($slotEnd)) {
                $bookingStart = $currentTime->format('H:i');
                $bookingEnd = $currentTime->copy()->addMinutes($duration)->format('H:i');

                // Check if this slot conflicts with existing bookings (including buffers)
                // Convert slot times to UTC for comparison with existing bookings
                $conflictsWithExisting = false;
                foreach ($existingBookings as $booking) {
                    $bookingStartWithBuffer = Carbon::parse($date->format('Y-m-d') . ' ' . $bookingStart, $scheduleTimezone)
                        ->subMinutes($beforeBuffer)
                        ->utc();
                    $bookingEndWithBuffer = Carbon::parse($date->format('Y-m-d') . ' ' . $bookingEnd, $scheduleTimezone)
                        ->addMinutes($afterBuffer)
                        ->utc();

                    $existingStart = Carbon::parse($booking['start'])->utc();
                    $existingEnd = Carbon::parse($booking['end'])->utc();

                    // Check for overlap
                    if ($bookingStartWithBuffer < $existingEnd && $bookingEndWithBuffer > $existingStart) {
                        $conflictsWithExisting = true;
                        break;
                    }
                }

                if (!$conflictsWithExisting) {
                    $bookableSlots[] = [
                        'start' => $bookingStart,
                        'end' => $bookingEnd,
                    ];
                }

                $currentTime->addMinutes($interval);
            }
        }

        return $bookableSlots;
    }
}
