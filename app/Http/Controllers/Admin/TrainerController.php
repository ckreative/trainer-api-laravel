<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class TrainerController extends Controller
{
    /**
     * List all trainers.
     */
    public function index(): JsonResponse
    {
        $trainers = User::where('role', Role::TRAINER)
            ->withCount(['bookings', 'eventTypes'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($trainer) {
                return [
                    'id' => $trainer->id,
                    'email' => $trainer->email,
                    'firstName' => $trainer->first_name,
                    'lastName' => $trainer->last_name,
                    'handle' => $trainer->handle,
                    'brandName' => $trainer->brand_name,
                    'avatarUrl' => $trainer->avatar_url,
                    'totalBookings' => $trainer->bookings_count,
                    'totalEventTypes' => $trainer->event_types_count,
                    'createdAt' => $trainer->created_at->toIso8601String(),
                    'lastLoginAt' => $trainer->last_login_at?->toIso8601String(),
                ];
            });

        return response()->json([
            'data' => $trainers,
            'total' => $trainers->count(),
        ]);
    }

    /**
     * Get a specific trainer's details.
     */
    public function show(string $id): JsonResponse
    {
        $trainer = User::where('role', Role::TRAINER)
            ->where('id', $id)
            ->withCount(['bookings', 'eventTypes'])
            ->first();

        if (! $trainer) {
            return response()->json([
                'error' => 'NOT_FOUND',
                'message' => 'Trainer not found',
                'statusCode' => 404,
            ], 404);
        }

        return response()->json([
            'id' => $trainer->id,
            'email' => $trainer->email,
            'firstName' => $trainer->first_name,
            'lastName' => $trainer->last_name,
            'handle' => $trainer->handle,
            'brandName' => $trainer->brand_name,
            'primaryColor' => $trainer->primary_color,
            'heroImageUrl' => $trainer->hero_image_url,
            'avatarUrl' => $trainer->avatar_url,
            'timezone' => $trainer->timezone,
            'totalBookings' => $trainer->bookings_count,
            'totalEventTypes' => $trainer->event_types_count,
            'createdAt' => $trainer->created_at->toIso8601String(),
            'lastLoginAt' => $trainer->last_login_at?->toIso8601String(),
        ]);
    }
}
