<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InviteAdminRequest;
use App\Mail\AdminInvitationMail;
use App\Models\AdminInvitation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class AdminInvitationController extends Controller
{
    /**
     * List all admin users and pending invitations.
     */
    public function index(): JsonResponse
    {
        // Get all admin users
        $users = User::where('role', Role::ADMIN)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'email' => $user->email,
                    'firstName' => $user->first_name,
                    'lastName' => $user->last_name,
                    'role' => $user->role->value,
                    'createdAt' => $user->created_at->toIso8601String(),
                    'lastLoginAt' => $user->last_login_at?->toIso8601String(),
                ];
            });

        // Get pending admin invitations
        $invitations = AdminInvitation::pending()
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
            'users' => $users,
            'invitations' => $invitations,
        ]);
    }

    /**
     * Send a new admin invitation.
     */
    public function store(InviteAdminRequest $request): JsonResponse
    {
        $invitation = AdminInvitation::create([
            'email' => $request->validated('email'),
            'first_name' => $request->validated('firstName'),
            'last_name' => $request->validated('lastName'),
            'invited_by' => $request->user()->id,
        ]);

        // Send invitation email
        Mail::to($invitation->email)->send(new AdminInvitationMail($invitation));

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
     * Cancel/delete a pending invitation.
     */
    public function destroy(string $id): JsonResponse
    {
        $invitation = AdminInvitation::find($id);

        if (! $invitation) {
            return response()->json([
                'error' => 'NOT_FOUND',
                'message' => 'Invitation not found',
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
        $invitation = AdminInvitation::pending()->find($id);

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

        Mail::to($invitation->email)->send(new AdminInvitationMail($invitation));

        return response()->json([
            'message' => 'Invitation resent successfully',
            'data' => [
                'id' => $invitation->id,
                'expiresAt' => $invitation->expires_at->toIso8601String(),
            ],
        ]);
    }
}
