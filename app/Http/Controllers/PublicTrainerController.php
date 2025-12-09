<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Resources\PublicEventTypeResource;
use App\Http\Resources\PublicTrainerResource;
use App\Models\AvailabilitySchedule;
use App\Models\EventType;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PublicTrainerController extends Controller
{
    /**
     * Get a trainer's public profile by handle.
     *
     * Returns the trainer's branding configuration for the booking app.
     */
    public function show(string $handle): PublicTrainerResource|JsonResponse
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

        return new PublicTrainerResource($trainer);
    }

    /**
     * Get a trainer's available event types.
     *
     * Returns only enabled event types for booking, including availability schedules.
     */
    public function eventTypes(string $handle): AnonymousResourceCollection|JsonResponse
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

        // Get the trainer's default availability schedule
        $defaultSchedule = AvailabilitySchedule::where('user_id', $trainer->id)
            ->where('is_default', true)
            ->first();

        $eventTypes = EventType::where('user_id', $trainer->id)
            ->where('enabled', true)
            ->with('availabilitySchedule')
            ->orderBy('title')
            ->get();

        // Attach the default schedule to the request for the resource to use
        return PublicEventTypeResource::collection($eventTypes)
            ->additional(['defaultSchedule' => $defaultSchedule]);
    }
}
