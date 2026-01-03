<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\AcceptInvitationRequest;
use App\Http\Resources\AuthResource;
use App\Models\AdminInvitation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSetupController extends Controller
{
    /**
     * Validate an admin invitation token and return invitation details.
     */
    public function show(string $token): JsonResponse
    {
        $invitation = AdminInvitation::where('token', $token)->first();

        if (! $invitation) {
            return response()->json([
                'error' => 'NOT_FOUND',
                'message' => 'Invitation not found',
                'statusCode' => 404,
            ], 404);
        }

        if ($invitation->isAccepted()) {
            return response()->json([
                'error' => 'ALREADY_ACCEPTED',
                'message' => 'This invitation has already been accepted',
                'statusCode' => 400,
            ], 400);
        }

        if ($invitation->isExpired()) {
            return response()->json([
                'error' => 'EXPIRED',
                'message' => 'This invitation has expired',
                'statusCode' => 400,
            ], 400);
        }

        return response()->json([
            'valid' => true,
            'email' => $invitation->email,
            'firstName' => $invitation->first_name,
            'lastName' => $invitation->last_name,
            'expiresAt' => $invitation->expires_at->toIso8601String(),
        ]);
    }

    /**
     * Accept an admin invitation and create the admin account.
     */
    public function accept(AcceptInvitationRequest $request, string $token): JsonResponse
    {
        $invitation = AdminInvitation::where('token', $token)->first();

        if (! $invitation || ! $invitation->isValid()) {
            return response()->json([
                'error' => 'INVALID_INVITATION',
                'message' => 'This invitation is invalid, expired, or already accepted',
                'statusCode' => 400,
            ], 400);
        }

        // Create the admin account in a transaction
        $user = DB::transaction(function () use ($invitation, $request) {
            // Use request name if provided, otherwise use invitation name
            $firstName = $request->validated('firstName') ?? $invitation->first_name;
            $lastName = $request->validated('lastName') ?? $invitation->last_name;

            // Create the user with ADMIN role
            $user = User::create([
                'email' => $invitation->email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'username' => Str::slug($firstName.'-'.$lastName).'-'.Str::random(4),
                'password' => Hash::make($request->validated('password')),
                'role' => Role::ADMIN,
                'email_verified' => true,
            ]);

            // Mark invitation as accepted
            $invitation->markAsAccepted();

            return $user;
        });

        // Create auth token for immediate login
        $token = $user->createToken('api')->plainTextToken;
        $expirationMinutes = config('sanctum.expiration') ?? 60;
        $expiresIn = $expirationMinutes * 60;

        return response()->json(
            new AuthResource($user, [
                'accessToken' => $token,
                'expiresIn' => $expiresIn,
            ])
        );
    }
}
