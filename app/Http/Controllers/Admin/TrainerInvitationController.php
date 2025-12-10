<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InviteTrainerRequest;
use App\Mail\TrainerInvitationMail;
use App\Models\TrainerInvitation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class TrainerInvitationController extends Controller
{
    /**
     * Send a new trainer invitation.
     */
    public function store(InviteTrainerRequest $request): JsonResponse
    {
        $invitation = TrainerInvitation::create([
            'email' => $request->validated('email'),
            'first_name' => $request->validated('firstName'),
            'last_name' => $request->validated('lastName'),
            'invited_by' => $request->user()->id,
        ]);

        // Send invitation email
        Mail::to($invitation->email)->send(new TrainerInvitationMail($invitation));

        return response()->json([
            'message' => 'Invitation sent successfully',
            'data' => [
                'id' => $invitation->id,
                'email' => $invitation->email,
                'firstName' => $invitation->first_name,
                'lastName' => $invitation->last_name,
                'expiresAt' => $invitation->expires_at->toIso8601String(),
                'createdAt' => $invitation->created_at->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * List all pending invitations.
     */
    public function index(): JsonResponse
    {
        $invitations = TrainerInvitation::pending()
            ->with('invitedBy:id,first_name,last_name')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($invitation) {
                return [
                    'id' => $invitation->id,
                    'email' => $invitation->email,
                    'firstName' => $invitation->first_name,
                    'lastName' => $invitation->last_name,
                    'expiresAt' => $invitation->expires_at->toIso8601String(),
                    'createdAt' => $invitation->created_at->toIso8601String(),
                    'invitedBy' => $invitation->invitedBy ? [
                        'id' => $invitation->invitedBy->id,
                        'name' => $invitation->invitedBy->first_name.' '.$invitation->invitedBy->last_name,
                    ] : null,
                ];
            });

        return response()->json([
            'data' => $invitations,
            'total' => $invitations->count(),
        ]);
    }

    /**
     * Cancel/delete a pending invitation.
     */
    public function destroy(string $id): JsonResponse
    {
        $invitation = TrainerInvitation::pending()->find($id);

        if (! $invitation) {
            return response()->json([
                'error' => 'NOT_FOUND',
                'message' => 'Invitation not found or already expired/accepted',
                'statusCode' => 404,
            ], 404);
        }

        $invitation->delete();

        return response()->json([
            'message' => 'Invitation cancelled successfully',
        ]);
    }

    /**
     * Resend an invitation email.
     */
    public function resend(string $id): JsonResponse
    {
        $invitation = TrainerInvitation::pending()->find($id);

        if (! $invitation) {
            return response()->json([
                'error' => 'NOT_FOUND',
                'message' => 'Invitation not found or already expired/accepted',
                'statusCode' => 404,
            ], 404);
        }

        // Reset expiration and send new email
        $invitation->update([
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($invitation->email)->send(new TrainerInvitationMail($invitation));

        return response()->json([
            'message' => 'Invitation resent successfully',
            'data' => [
                'id' => $invitation->id,
                'expiresAt' => $invitation->expires_at->toIso8601String(),
            ],
        ]);
    }
}
