<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateEventTypeRequest;
use App\Http\Requests\ToggleEventTypeRequest;
use App\Http\Requests\UpdateEventTypeRequest;
use App\Http\Resources\EventTypeResource;
use App\Models\EventType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventTypesController extends Controller
{
    /**
     * Get all event types for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $query = EventType::where('user_id', $user->id);

            // Filter by enabled status if provided
            if ($request->has('enabled')) {
                $enabled = filter_var($request->query('enabled'), FILTER_VALIDATE_BOOLEAN);
                $query->where('enabled', $enabled);
            }

            // Get pagination parameters
            $limit = min((int) $request->query('limit', 20), 100);
            $offset = max((int) $request->query('offset', 0), 0);

            $total = $query->count();
            $eventTypes = $query->skip($offset)->take($limit)->get();

            return response()->json([
                'eventTypes' => EventTypeResource::collection($eventTypes),
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'INTERNAL_ERROR',
                'message' => 'An error occurred while fetching event types',
                'statusCode' => 500,
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }

    /**
     * Create a new event type
     */
    public function store(CreateEventTypeRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $validated = $request->validated();

            // Check if URL is already in use by this user
            $existingEventType = EventType::where('user_id', $user->id)
                ->where('url', $validated['url'])
                ->first();

            if ($existingEventType) {
                return response()->json([
                    'error' => 'DUPLICATE_URL',
                    'message' => 'An event type with this URL already exists',
                    'statusCode' => 400,
                    'timestamp' => now()->toIso8601String(),
                ], 400);
            }

            $eventType = EventType::create([
                'user_id' => $user->id,
                'title' => $validated['title'],
                'url' => $validated['url'],
                'description' => $validated['description'] ?? null,
                'duration' => $validated['duration'],
                'location' => $validated['location'] ?? null,
                'custom_location' => $validated['customLocation'] ?? null,
                'enabled' => $validated['enabled'] ?? true,
            ]);

            return response()->json(
                new EventTypeResource($eventType),
                201
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'INTERNAL_ERROR',
                'message' => 'An error occurred while creating the event type',
                'statusCode' => 500,
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }

    /**
     * Get a specific event type by ID
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();

            $eventType = EventType::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$eventType) {
                return response()->json([
                    'error' => 'NOT_FOUND',
                    'message' => 'Event type not found',
                    'statusCode' => 404,
                    'timestamp' => now()->toIso8601String(),
                ], 404);
            }

            return response()->json(
                new EventTypeResource($eventType)
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'INTERNAL_ERROR',
                'message' => 'An error occurred while fetching the event type',
                'statusCode' => 500,
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }

    /**
     * Update an event type
     */
    public function update(UpdateEventTypeRequest $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();
            $validated = $request->validated();

            $eventType = EventType::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$eventType) {
                return response()->json([
                    'error' => 'NOT_FOUND',
                    'message' => 'Event type not found',
                    'statusCode' => 404,
                    'timestamp' => now()->toIso8601String(),
                ], 404);
            }

            // Check if URL is already in use by another event type for this user
            if (isset($validated['url']) && $validated['url'] !== $eventType->url) {
                $existingEventType = EventType::where('user_id', $user->id)
                    ->where('url', $validated['url'])
                    ->where('id', '!=', $id)
                    ->first();

                if ($existingEventType) {
                    return response()->json([
                        'error' => 'DUPLICATE_URL',
                        'message' => 'An event type with this URL already exists',
                        'statusCode' => 400,
                        'timestamp' => now()->toIso8601String(),
                    ], 400);
                }
            }

            // Update basic fields
            $updateData = [];
            foreach (['title', 'url', 'description', 'duration', 'enabled', 'location', 'customLocation'] as $field) {
                $snakeField = \Illuminate\Support\Str::snake($field);
                if (isset($validated[$field])) {
                    $updateData[$snakeField] = $validated[$field];
                }
            }

            // Update multiple durations
            if (isset($validated['allowMultipleDurations'])) {
                $updateData['allow_multiple_durations'] = $validated['allowMultipleDurations'];
            }
            if (isset($validated['multipleDurationOptions'])) {
                $updateData['multiple_duration_options'] = $validated['multipleDurationOptions'];
            }

            // Update buffers and notice
            if (isset($validated['beforeEventBuffer'])) {
                $updateData['before_event_buffer'] = $validated['beforeEventBuffer'];
            }
            if (isset($validated['afterEventBuffer'])) {
                $updateData['after_event_buffer'] = $validated['afterEventBuffer'];
            }
            if (isset($validated['minimumNotice'])) {
                $updateData['minimum_notice'] = $validated['minimumNotice'];
            }
            if (isset($validated['timeSlotInterval'])) {
                $updateData['time_slot_interval'] = $validated['timeSlotInterval'];
            }

            // Update booking frequency limits
            if (isset($validated['limitBookingFrequency'])) {
                $updateData['limit_booking_frequency'] = $validated['limitBookingFrequency'];
            }
            if (isset($validated['bookingFrequencyLimit'])) {
                $updateData['booking_frequency_limit'] = $validated['bookingFrequencyLimit'];
            }

            // Update other limits
            if (isset($validated['onlyFirstSlotPerDay'])) {
                $updateData['only_first_slot_per_day'] = $validated['onlyFirstSlotPerDay'];
            }

            // Update total duration limits
            if (isset($validated['limitTotalDuration'])) {
                $updateData['limit_total_duration'] = $validated['limitTotalDuration'];
            }
            if (isset($validated['totalDurationLimit'])) {
                $updateData['total_duration_limit'] = $validated['totalDurationLimit'];
            }

            // Update upcoming bookings limit
            if (isset($validated['limitUpcomingBookings'])) {
                $updateData['limit_upcoming_bookings'] = $validated['limitUpcomingBookings'];
            }
            if (isset($validated['upcomingBookingsLimit'])) {
                $updateData['upcoming_bookings_limit'] = $validated['upcomingBookingsLimit'];
            }

            // Update future bookings limit
            if (isset($validated['limitFutureBookings'])) {
                $updateData['limit_future_bookings'] = $validated['limitFutureBookings'];
            }
            if (isset($validated['futureBookingsLimit'])) {
                $updateData['future_bookings_limit'] = $validated['futureBookingsLimit'];
            }

            // Update availability schedule
            if (isset($validated['availabilityScheduleId'])) {
                $updateData['availability_schedule_id'] = $validated['availabilityScheduleId'];
            }

            $eventType->update($updateData);

            return response()->json(
                new EventTypeResource($eventType->fresh())
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'INTERNAL_ERROR',
                'message' => 'An error occurred while updating the event type',
                'statusCode' => 500,
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }

    /**
     * Delete an event type
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();

            $eventType = EventType::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$eventType) {
                return response()->json([
                    'error' => 'NOT_FOUND',
                    'message' => 'Event type not found',
                    'statusCode' => 404,
                    'timestamp' => now()->toIso8601String(),
                ], 404);
            }

            // Check if event type has existing bookings
            // Assuming a relationship exists: bookings()
            if (method_exists($eventType, 'bookings') && $eventType->bookings()->count() > 0) {
                return response()->json([
                    'error' => 'CONFLICT',
                    'message' => 'Cannot delete event type with existing bookings',
                    'statusCode' => 409,
                    'timestamp' => now()->toIso8601String(),
                ], 409);
            }

            $eventType->delete();

            return response()->json([
                'message' => 'Event type deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'INTERNAL_ERROR',
                'message' => 'An error occurred while deleting the event type',
                'statusCode' => 500,
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }

    /**
     * Duplicate an event type
     */
    public function duplicate(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();

            $eventType = EventType::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$eventType) {
                return response()->json([
                    'error' => 'NOT_FOUND',
                    'message' => 'Event type not found',
                    'statusCode' => 404,
                    'timestamp' => now()->toIso8601String(),
                ], 404);
            }

            // Generate unique URL for the duplicate
            $baseUrl = $eventType->url;
            $newUrl = $baseUrl . '-copy';
            $counter = 1;

            while (EventType::where('user_id', $user->id)->where('url', $newUrl)->exists()) {
                $counter++;
                $newUrl = $baseUrl . '-copy-' . $counter;
            }

            // Create duplicate with all settings
            $duplicatedEventType = EventType::create([
                'user_id' => $user->id,
                'title' => $eventType->title . ' (Copy)',
                'url' => $newUrl,
                'description' => $eventType->description,
                'duration' => $eventType->duration,
                'enabled' => false, // Duplicates start as disabled
                'allow_multiple_durations' => $eventType->allow_multiple_durations,
                'multiple_duration_options' => $eventType->multiple_duration_options,
                'location' => $eventType->location,
                'custom_location' => $eventType->custom_location,
                'before_event_buffer' => $eventType->before_event_buffer,
                'after_event_buffer' => $eventType->after_event_buffer,
                'minimum_notice' => $eventType->minimum_notice,
                'time_slot_interval' => $eventType->time_slot_interval,
                'limit_booking_frequency' => $eventType->limit_booking_frequency,
                'booking_frequency_limit' => $eventType->booking_frequency_limit,
                'only_first_slot_per_day' => $eventType->only_first_slot_per_day,
                'limit_total_duration' => $eventType->limit_total_duration,
                'total_duration_limit' => $eventType->total_duration_limit,
                'limit_upcoming_bookings' => $eventType->limit_upcoming_bookings,
                'upcoming_bookings_limit' => $eventType->upcoming_bookings_limit,
                'limit_future_bookings' => $eventType->limit_future_bookings,
                'future_bookings_limit' => $eventType->future_bookings_limit,
                'availability_schedule_id' => $eventType->availability_schedule_id,
            ]);

            return response()->json(
                new EventTypeResource($duplicatedEventType),
                201
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'INTERNAL_ERROR',
                'message' => 'An error occurred while duplicating the event type',
                'statusCode' => 500,
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }

    /**
     * Toggle event type enabled/disabled status
     */
    public function toggle(ToggleEventTypeRequest $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();
            $validated = $request->validated();

            $eventType = EventType::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$eventType) {
                return response()->json([
                    'error' => 'NOT_FOUND',
                    'message' => 'Event type not found',
                    'statusCode' => 404,
                    'timestamp' => now()->toIso8601String(),
                ], 404);
            }

            $eventType->update(['enabled' => $validated['enabled']]);

            return response()->json(
                new EventTypeResource($eventType->fresh())
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'INTERNAL_ERROR',
                'message' => 'An error occurred while toggling the event type status',
                'statusCode' => 500,
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }
}
