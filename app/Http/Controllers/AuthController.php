<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Resources\AuthResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Authenticate user and return tokens
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            return response()->json([
                'error' => 'UNAUTHORIZED',
                'message' => 'Invalid credentials',
                'statusCode' => 401,
                'timestamp' => now()->toIso8601String(),
            ], 401);
        }

        // Update last login timestamp
        $user->update(['last_login_at' => now()]);

        // Create token with expiration
        $tokenName = $request->validated('rememberMe', false) ? 'remember-me' : 'api';
        $token = $user->createToken($tokenName)->plainTextToken;

        // Get token expiration from config (convert minutes to seconds)
        $expirationMinutes = config('sanctum.expiration') ?? 60;
        $expiresIn = $expirationMinutes * 60; // Convert to seconds

        return response()->json(
            new AuthResource($user, [
                'accessToken' => $token,
                'expiresIn' => $expiresIn,
            ])
        );
    }

    /**
     * Logout user and invalidate session
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }

    /**
     * Get current authenticated user profile
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json(new UserResource($request->user()));
    }

    /**
     * Request password reset email
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Always return success for security (don't reveal if email exists)
        return response()->json([
            'message' => 'If an account exists with this email, a password reset link has been sent',
        ]);
    }

    /**
     * Reset password using reset token
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            [
                'email' => $request->input('email'),
                'password' => $request->validated('newPassword'),
                'password_confirmation' => $request->validated('newPassword'),
                'token' => $request->validated('token'),
            ],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Password has been reset successfully',
            ]);
        }

        return response()->json([
            'error' => 'UNAUTHORIZED',
            'message' => 'Invalid or expired reset token',
            'statusCode' => 401,
            'timestamp' => now()->toIso8601String(),
        ], 401);
    }
}


