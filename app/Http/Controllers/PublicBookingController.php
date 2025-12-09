<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\CreatePublicBookingRequest;
use App\Http\Resources\BookingResource;
use App\Mail\BookingConfirmation;
use App\Models\Booking;
use App\Models\EventType;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class PublicBookingController extends Controller
{
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

        if (! $trainer) {
            return response()->json([
                'error' => 'Not Found',
                'message' => 'Trainer not found',
            ], 404);
        }

        $validated = $request->validated();

        // Verify the event type belongs to this trainer
        $eventType = EventType::where('id', $validated['eventTypeId'])
            ->where('user_id', $trainer->id)
            ->first();

        if (! $eventType) {
            return response()->json([
                'error' => 'Not Found',
                'message' => 'Event type not found for this trainer',
            ], 404);
        }

        // Calculate end time based on event type duration
        $startTime = new \DateTime($validated['startTime']);
        $endTime = (clone $startTime)->modify("+{$eventType->duration} minutes");

        $booking = Booking::create([
            'user_id' => $trainer->id,
            'event_type_id' => $eventType->id,
            'title' => $eventType->name.' with '.$validated['attendeeName'],
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
