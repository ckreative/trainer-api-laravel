<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingsController extends Controller
{
    /**
     * Get all bookings for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $query = Booking::where('user_id', $user->id)
                ->with('eventType');

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->query('status'));
            }

            // Get pagination parameters
            $limit = min((int) $request->query('limit', 20), 100);
            $offset = max((int) $request->query('offset', 0), 0);

            // Default ordering: upcoming first, then by start_time
            $query->orderByRaw("CASE WHEN status = 'upcoming' THEN 0 WHEN status = 'unconfirmed' THEN 1 ELSE 2 END")
                ->orderBy('start_time', 'desc');

            $total = $query->count();
            $bookings = $query->skip($offset)->take($limit)->get();

            return response()->json([
                'bookings' => BookingResource::collection($bookings),
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'INTERNAL_ERROR',
                'message' => 'An error occurred while fetching bookings',
                'statusCode' => 500,
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }

    /**
     * Get a specific booking by ID
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();

            $booking = Booking::where('id', $id)
                ->where('user_id', $user->id)
                ->with('eventType')
                ->first();

            if (!$booking) {
                return response()->json([
                    'error' => 'NOT_FOUND',
                    'message' => 'Booking not found',
                    'statusCode' => 404,
                    'timestamp' => now()->toIso8601String(),
                ], 404);
            }

            return response()->json(
                new BookingResource($booking)
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'INTERNAL_ERROR',
                'message' => 'An error occurred while fetching the booking',
                'statusCode' => 500,
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }

    /**
     * Create a new booking
     */
    public function store(CreateBookingRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $validated = $request->validated();

            $booking = Booking::create([
                'user_id' => $user->id,
                'event_type_id' => $validated['eventTypeId'],
                'title' => $validated['title'],
                'start_time' => $validated['startTime'],
                'end_time' => $validated['endTime'],
                'status' => $validated['status'] ?? 'upcoming',
                'attendee_name' => $validated['attendeeName'],
                'attendee_email' => $validated['attendeeEmail'],
                'location' => $validated['location'] ?? null,
                'meeting_url' => $validated['meetingUrl'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'timezone' => $validated['timezone'] ?? 'UTC',
                'is_recurring' => $validated['isRecurring'] ?? false,
                'recurrence_rule' => $validated['recurrenceRule'] ?? null,
            ]);

            $booking->load('eventType');

            return response()->json(
                new BookingResource($booking),
                201
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'INTERNAL_ERROR',
                'message' => 'An error occurred while creating the booking',
                'statusCode' => 500,
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }

    /**
     * Update a booking
     */
    public function update(UpdateBookingRequest $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();
            $validated = $request->validated();

            $booking = Booking::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$booking) {
                return response()->json([
                    'error' => 'NOT_FOUND',
                    'message' => 'Booking not found',
                    'statusCode' => 404,
                    'timestamp' => now()->toIso8601String(),
                ], 404);
            }

            $updateData = [];

            if (isset($validated['title'])) {
                $updateData['title'] = $validated['title'];
            }
            if (isset($validated['startTime'])) {
                $updateData['start_time'] = $validated['startTime'];
            }
            if (isset($validated['endTime'])) {
                $updateData['end_time'] = $validated['endTime'];
            }
            if (isset($validated['status'])) {
                $updateData['status'] = $validated['status'];
            }
            if (isset($validated['attendeeName'])) {
                $updateData['attendee_name'] = $validated['attendeeName'];
            }
            if (isset($validated['attendeeEmail'])) {
                $updateData['attendee_email'] = $validated['attendeeEmail'];
            }
            if (array_key_exists('location', $validated)) {
                $updateData['location'] = $validated['location'];
            }
            if (array_key_exists('meetingUrl', $validated)) {
                $updateData['meeting_url'] = $validated['meetingUrl'];
            }
            if (array_key_exists('notes', $validated)) {
                $updateData['notes'] = $validated['notes'];
            }
            if (isset($validated['timezone'])) {
                $updateData['timezone'] = $validated['timezone'];
            }
            if (isset($validated['isRecurring'])) {
                $updateData['is_recurring'] = $validated['isRecurring'];
            }
            if (array_key_exists('recurrenceRule', $validated)) {
                $updateData['recurrence_rule'] = $validated['recurrenceRule'];
            }

            $booking->update($updateData);

            return response()->json(
                new BookingResource($booking->fresh()->load('eventType'))
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'INTERNAL_ERROR',
                'message' => 'An error occurred while updating the booking',
                'statusCode' => 500,
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }

    /**
     * Delete a booking
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();

            $booking = Booking::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$booking) {
                return response()->json([
                    'error' => 'NOT_FOUND',
                    'message' => 'Booking not found',
                    'statusCode' => 404,
                    'timestamp' => now()->toIso8601String(),
                ], 404);
            }

            $booking->delete();

            return response()->json([
                'message' => 'Booking deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'INTERNAL_ERROR',
                'message' => 'An error occurred while deleting the booking',
                'statusCode' => 500,
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }

    /**
     * Cancel a booking (soft action - sets status to cancelled)
     */
    public function cancel(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();

            $booking = Booking::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$booking) {
                return response()->json([
                    'error' => 'NOT_FOUND',
                    'message' => 'Booking not found',
                    'statusCode' => 404,
                    'timestamp' => now()->toIso8601String(),
                ], 404);
            }

            if ($booking->status === 'cancelled') {
                return response()->json([
                    'error' => 'BAD_REQUEST',
                    'message' => 'Booking is already cancelled',
                    'statusCode' => 400,
                    'timestamp' => now()->toIso8601String(),
                ], 400);
            }

            $booking->update(['status' => 'cancelled']);

            return response()->json(
                new BookingResource($booking->fresh()->load('eventType'))
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'INTERNAL_ERROR',
                'message' => 'An error occurred while cancelling the booking',
                'statusCode' => 500,
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }
}
