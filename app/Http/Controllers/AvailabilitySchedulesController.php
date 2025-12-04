<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateAvailabilityScheduleRequest;
use App\Http\Requests\UpdateAvailabilityScheduleRequest;
use App\Http\Resources\AvailabilityScheduleResource;
use App\Models\AvailabilitySchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvailabilitySchedulesController extends Controller
{
    /**
     * Get all availability schedules for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $query = AvailabilitySchedule::where('user_id', $user->id);

            // Filter by isDefault if provided
            if ($request->has('isDefault')) {
                $isDefault = filter_var($request->query('isDefault'), FILTER_VALIDATE_BOOLEAN);
                $query->where('is_default', $isDefault);
            }

            // Get pagination parameters
            $limit = min((int) $request->query('limit', 20), 100);
            $offset = max((int) $request->query('offset', 0), 0);

            $total = $query->count();
            $schedules = $query->skip($offset)->take($limit)->get();

            return response()->json([
                'schedules' => AvailabilityScheduleResource::collection($schedules),
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'INTERNAL_ERROR',
                'message' => 'An error occurred while fetching availability schedules',
                'statusCode' => 500,
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }

    /**
     * Create a new availability schedule
     */
    public function store(CreateAvailabilityScheduleRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $validated = $request->validated();

            // If setting as default, unset other defaults
            if ($validated['isDefault'] ?? false) {
                AvailabilitySchedule::where('user_id', $user->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            $schedule = AvailabilitySchedule::create([
                'user_id' => $user->id,
                'name' => $validated['name'],
                'is_default' => $validated['isDefault'] ?? false,
                'timezone' => $validated['timezone'],
                'schedule' => $validated['schedule'],
            ]);

            return response()->json(
                new AvailabilityScheduleResource($schedule),
                201
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'INTERNAL_ERROR',
                'message' => 'An error occurred while creating the availability schedule',
                'statusCode' => 500,
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }

    /**
     * Get a specific availability schedule by ID
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();

            $schedule = AvailabilitySchedule::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$schedule) {
                return response()->json([
                    'error' => 'NOT_FOUND',
                    'message' => 'Availability schedule not found',
                    'statusCode' => 404,
                    'timestamp' => now()->toIso8601String(),
                ], 404);
            }

            return response()->json(
                new AvailabilityScheduleResource($schedule)
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'INTERNAL_ERROR',
                'message' => 'An error occurred while fetching the availability schedule',
                'statusCode' => 500,
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }

    /**
     * Update an availability schedule
     */
    public function update(UpdateAvailabilityScheduleRequest $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();
            $validated = $request->validated();

            $schedule = AvailabilitySchedule::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$schedule) {
                return response()->json([
                    'error' => 'NOT_FOUND',
                    'message' => 'Availability schedule not found',
                    'statusCode' => 404,
                    'timestamp' => now()->toIso8601String(),
                ], 404);
            }

            // If setting as default, unset other defaults
            if (isset($validated['isDefault']) && $validated['isDefault']) {
                AvailabilitySchedule::where('user_id', $user->id)
                    ->where('id', '!=', $id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            $schedule->update([
                'name' => $validated['name'] ?? $schedule->name,
                'is_default' => $validated['isDefault'] ?? $schedule->is_default,
                'timezone' => $validated['timezone'] ?? $schedule->timezone,
                'schedule' => $validated['schedule'] ?? $schedule->schedule,
            ]);

            return response()->json(
                new AvailabilityScheduleResource($schedule->fresh())
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'INTERNAL_ERROR',
                'message' => 'An error occurred while updating the availability schedule',
                'statusCode' => 500,
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }

    /**
     * Delete an availability schedule
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();

            $schedule = AvailabilitySchedule::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$schedule) {
                return response()->json([
                    'error' => 'NOT_FOUND',
                    'message' => 'Availability schedule not found',
                    'statusCode' => 404,
                    'timestamp' => now()->toIso8601String(),
                ], 404);
            }

            // Check if this is the default schedule
            if ($schedule->is_default) {
                return response()->json([
                    'error' => 'CONFLICT',
                    'message' => 'Cannot delete the default availability schedule',
                    'statusCode' => 409,
                    'timestamp' => now()->toIso8601String(),
                ], 409);
            }

            // Check if schedule is in use by event types
            // Assuming a relationship exists: eventTypes()
            if (method_exists($schedule, 'eventTypes') && $schedule->eventTypes()->count() > 0) {
                return response()->json([
                    'error' => 'CONFLICT',
                    'message' => 'Cannot delete schedule that is in use by event types',
                    'statusCode' => 409,
                    'timestamp' => now()->toIso8601String(),
                ], 409);
            }

            $schedule->delete();

            return response()->json([
                'message' => 'Availability schedule deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'INTERNAL_ERROR',
                'message' => 'An error occurred while deleting the availability schedule',
                'statusCode' => 500,
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }

    /**
     * Duplicate an availability schedule
     */
    public function duplicate(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();

            $schedule = AvailabilitySchedule::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$schedule) {
                return response()->json([
                    'error' => 'NOT_FOUND',
                    'message' => 'Availability schedule not found',
                    'statusCode' => 404,
                    'timestamp' => now()->toIso8601String(),
                ], 404);
            }

            // Create a duplicate with a modified name
            $duplicatedSchedule = AvailabilitySchedule::create([
                'user_id' => $user->id,
                'name' => $schedule->name . ' (Copy)',
                'is_default' => false,
                'timezone' => $schedule->timezone,
                'schedule' => $schedule->schedule,
            ]);

            return response()->json(
                new AvailabilityScheduleResource($duplicatedSchedule),
                201
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'INTERNAL_ERROR',
                'message' => 'An error occurred while duplicating the availability schedule',
                'statusCode' => 500,
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }

    /**
     * Set an availability schedule as the default
     */
    public function setDefault(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();

            $schedule = AvailabilitySchedule::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$schedule) {
                return response()->json([
                    'error' => 'NOT_FOUND',
                    'message' => 'Availability schedule not found',
                    'statusCode' => 404,
                    'timestamp' => now()->toIso8601String(),
                ], 404);
            }

            // Unset all other defaults for this user
            AvailabilitySchedule::where('user_id', $user->id)
                ->where('id', '!=', $id)
                ->where('is_default', true)
                ->update(['is_default' => false]);

            // Set this one as default
            $schedule->update(['is_default' => true]);

            return response()->json(
                new AvailabilityScheduleResource($schedule->fresh())
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'INTERNAL_ERROR',
                'message' => 'An error occurred while setting the default schedule',
                'statusCode' => 500,
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }
}
