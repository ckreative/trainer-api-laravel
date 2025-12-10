<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\AcceptInvitationRequest;
use App\Http\Resources\AuthResource;
use App\Models\TrainerInvitation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    /**
     * Validate an invitation token and return invitation details.
     */
    public function show(string $token): JsonResponse
    {
        $invitation = TrainerInvitation::where('token', $token)->first();

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
     * Accept an invitation and create the trainer account.
     */
    public function accept(AcceptInvitationRequest $request, string $token): JsonResponse
    {
        $invitation = TrainerInvitation::where('token', $token)->first();

        if (! $invitation || ! $invitation->isValid()) {
            return response()->json([
                'error' => 'INVALID_INVITATION',
                'message' => 'This invitation is invalid, expired, or already accepted',
                'statusCode' => 400,
            ], 400);
        }

        // Create the trainer account in a transaction
        $user = DB::transaction(function () use ($invitation, $request) {
            // Create the user
            $user = User::create([
                'email' => $invitation->email,
                'first_name' => $invitation->first_name,
                'last_name' => $invitation->last_name,
                'username' => Str::slug($invitation->first_name.'-'.$invitation->last_name).'-'.Str::random(4),
                'password' => Hash::make($request->validated('password')),
                'role' => Role::TRAINER,
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
