<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\CreatePublicBookingRequest;
use App\Http\Resources\BookingResource;
use App\Mail\BookingConfirmation;
use App\Models\Booking;
use App\Models\EventType;
use App\Models\User;
use App\Services\AvailabilityCalculator;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PublicBookingController extends Controller
{
    public function __construct(
        private AvailabilityCalculator $availabilityCalculator
    ) {}

    /**
     * Get available time slots for an event type on a specific date.
     *
     * Public endpoint for the booking app to fetch available slots.
     */
    public function availableSlots(string $handle, string $eventTypeId, Request $request): JsonResponse
    {
        $trainer = User::where('handle', $handle)
            ->where('role', Role::TRAINER)
            ->first();

        if (!$trainer) {
            return response()->json([
                'error' => 'Not Found',
                'message' => 'Trainer not found',
            ], 404);
        }

        // Validate date parameter
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $date = Carbon::parse($request->input('date'));

        // Verify the event type belongs to this trainer and is enabled
        $eventType = EventType::where('id', $eventTypeId)
            ->where('user_id', $trainer->id)
            ->where('enabled', true)
            ->with('availabilitySchedule')
            ->first();

        if (!$eventType) {
            return response()->json([
                'error' => 'Not Found',
                'message' => 'Event type not found or not available',
            ], 404);
        }

        if (!$eventType->availabilitySchedule) {
            return response()->json([
                'error' => 'Configuration Error',
                'message' => 'No availability schedule configured for this event type',
            ], 400);
        }

        // Get existing bookings for this trainer on this date
        $existingBookings = Booking::where('user_id', $trainer->id)
            ->whereDate('start_time', $date)
            ->whereNotIn('status', ['cancelled'])
            ->get()
            ->map(fn($booking) => [
                'start' => $booking->start_time->format('Y-m-d H:i:s'),
                'end' => $booking->end_time->format('Y-m-d H:i:s'),
            ])
            ->toArray();

        // Get bookable slots using the calculator
        $slots = $this->availabilityCalculator->getBookableSlots(
            $eventType,
            $date,
            $existingBookings
        );

        return response()->json([
            'date' => $date->format('Y-m-d'),
            'eventType' => [
                'id' => $eventType->id,
                'title' => $eventType->title,
                'duration' => $eventType->duration,
            ],
            'timezone' => $eventType->availabilitySchedule->timezone,
            'slots' => $slots,
        ]);
    }

    /**
     * Create a new booking for a trainer.
     *
     * Public endpoint for the booking app to create bookings.
     */
    public function store(string $handle, CreatePublicBookingRequest $request): BookingResource|JsonResponse
    {
        $trainer = User::where('handle', $handle)
            ->where('role', Role::TRAINER)
            ->first();

        if (!$trainer) {
            return response()->json([
                'error' => 'Not Found',
                'message' => 'Trainer not found',
            ], 404);
        }

        $validated = $request->validated();

        // Verify the event type belongs to this trainer and is enabled
        $eventType = EventType::where('id', $validated['eventTypeId'])
            ->where('user_id', $trainer->id)
            ->where('enabled', true)
            ->with('availabilitySchedule')
            ->first();

        if (!$eventType) {
            return response()->json([
                'error' => 'Not Found',
                'message' => 'Event type not found for this trainer',
            ], 404);
        }

        // Calculate start and end times
        $startTime = Carbon::parse($validated['startTime']);
        $endTime = $startTime->copy()->addMinutes($eventType->duration);

        // Validate the requested slot is available
        if ($eventType->availabilitySchedule) {
            // Convert start time to schedule's timezone to compare with available slots
            $scheduleTimezone = $eventType->availabilitySchedule->timezone ?? 'UTC';
            $requestedSlotTime = $startTime->copy()->setTimezone($scheduleTimezone)->format('H:i');
            // Get existing bookings for this trainer on this date
            $existingBookings = Booking::where('user_id', $trainer->id)
                ->whereDate('start_time', $startTime->toDateString())
                ->whereNotIn('status', ['cancelled'])
                ->get()
                ->map(fn($booking) => [
                    'start' => $booking->start_time->format('Y-m-d H:i:s'),
                    'end' => $booking->end_time->format('Y-m-d H:i:s'),
                ])
                ->toArray();

            // Get available slots
            $availableSlots = $this->availabilityCalculator->getBookableSlots(
                $eventType,
                $startTime,
                $existingBookings
            );

            // Check if the requested time is in the available slots
            $isSlotAvailable = collect($availableSlots)->contains(fn($slot) => $slot['start'] === $requestedSlotTime);

            if (!$isSlotAvailable) {
                return response()->json([
                    'error' => 'Slot Unavailable',
                    'message' => 'The requested time slot is no longer available',
                ], 409);
            }
        }

        $booking = Booking::create([
            'user_id' => $trainer->id,
            'event_type_id' => $eventType->id,
            'title' => $eventType->title . ' with ' . $validated['attendeeName'],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'unconfirmed',
            'attendee_name' => $validated['attendeeName'],
            'attendee_email' => $validated['attendeeEmail'],
            'attendee_phone' => $validated['attendeePhone'] ?? null,
            'location' => $eventType->location ?? null,
            'meeting_url' => $eventType->meeting_url ?? null,
            'notes' => $validated['notes'] ?? null,
            'timezone' => $validated['timezone'] ?? 'UTC',
            'is_recurring' => false,
            'recurrence_rule' => null,
        ]);

        $booking->load(['eventType', 'user']);

        // Send confirmation email to attendee
        Mail::to($booking->attendee_email)->send(new BookingConfirmation($booking));

        return new BookingResource($booking);
    }
}
