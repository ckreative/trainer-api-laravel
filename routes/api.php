<?php

use App\Http\Controllers\Admin\TrainerController;
use App\Http\Controllers\Admin\TrainerInvitationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AvailabilitySchedulesController;
use App\Http\Controllers\BookingsController;
use App\Http\Controllers\BrandingController;
use App\Http\Controllers\EventTypesController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\PublicBookingController;
use App\Http\Controllers\PublicTrainerController;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::prefix('auth')->group(function () {
    // Public auth routes with rate limiting
    Route::middleware('throttle:6,1')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });

    // Protected auth routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });
});

// ============================================
// Availability Schedules API Routes
// Generated from: docs/api/availability-schedules-api.yaml
// Generated on: 2025-11-11
// ============================================

Route::middleware('auth:sanctum')->group(function () {
    // List all availability schedules for authenticated user
    Route::get('/availability-schedules', [AvailabilitySchedulesController::class, 'index']);

    // Create a new availability schedule
    Route::post('/availability-schedules', [AvailabilitySchedulesController::class, 'store']);

    // Get a specific availability schedule by ID
    Route::get('/availability-schedules/{id}', [AvailabilitySchedulesController::class, 'show']);

    // Update an availability schedule
    Route::put('/availability-schedules/{id}', [AvailabilitySchedulesController::class, 'update']);

    // Delete an availability schedule
    Route::delete('/availability-schedules/{id}', [AvailabilitySchedulesController::class, 'destroy']);

    // Duplicate an availability schedule
    Route::post('/availability-schedules/{id}/duplicate', [AvailabilitySchedulesController::class, 'duplicate']);

    // Set an availability schedule as default
    Route::patch('/availability-schedules/{id}/set-default', [AvailabilitySchedulesController::class, 'setDefault']);
});

// ============================================
// Event Types API Routes
// Generated from: docs/api/event-types-api.yaml
// Generated on: 2025-11-11
// ============================================

Route::middleware('auth:sanctum')->group(function () {
    // List all event types for authenticated user
    Route::get('/event-types', [EventTypesController::class, 'index']);

    // Create a new event type
    Route::post('/event-types', [EventTypesController::class, 'store']);

    // Get a specific event type by ID
    Route::get('/event-types/{id}', [EventTypesController::class, 'show']);

    // Update an event type
    Route::put('/event-types/{id}', [EventTypesController::class, 'update']);

    // Delete an event type
    Route::delete('/event-types/{id}', [EventTypesController::class, 'destroy']);

    // Duplicate an event type
    Route::post('/event-types/{id}/duplicate', [EventTypesController::class, 'duplicate']);

    // Toggle event type enabled/disabled status
    Route::patch('/event-types/{id}/toggle', [EventTypesController::class, 'toggle']);
});

// ============================================
// Bookings API Routes
// ============================================

Route::middleware('auth:sanctum')->group(function () {
    // List all bookings for authenticated user
    Route::get('/bookings', [BookingsController::class, 'index']);

    // Create a new booking
    Route::post('/bookings', [BookingsController::class, 'store']);

    // Get a specific booking by ID
    Route::get('/bookings/{id}', [BookingsController::class, 'show']);

    // Update a booking
    Route::put('/bookings/{id}', [BookingsController::class, 'update']);

    // Delete a booking
    Route::delete('/bookings/{id}', [BookingsController::class, 'destroy']);

    // Cancel a booking (soft action)
    Route::patch('/bookings/{id}/cancel', [BookingsController::class, 'cancel']);
});

// ============================================
// Branding API Routes (Dashboard App)
// ============================================

Route::middleware('auth:sanctum')->group(function () {
    // Get branding settings for authenticated user
    Route::get('/branding', [BrandingController::class, 'show']);

    // Update branding settings
    Route::put('/branding', [BrandingController::class, 'update']);
});

// ============================================
// Public API Routes (Booking App)
// Authenticated via X-App-Key header
// ============================================

Route::middleware('app.key')->prefix('public')->group(function () {
    // Get trainer public profile by handle
    Route::get('/trainers/{handle}', [PublicTrainerController::class, 'show']);

    // Get trainer's available event types
    Route::get('/trainers/{handle}/event-types', [PublicTrainerController::class, 'eventTypes']);

    // Create a booking for a trainer
    Route::post('/trainers/{handle}/bookings', [PublicBookingController::class, 'store']);
});

// ============================================
// Public Invitation Routes (Account Setup)
// ============================================

Route::prefix('invitations')->group(function () {
    // Validate invitation token
    Route::get('/{token}', [InvitationController::class, 'show']);

    // Accept invitation and create account
    Route::post('/{token}/accept', [InvitationController::class, 'accept']);
});

// ============================================
// Admin API Routes
// Protected by auth:sanctum + admin middleware
// ============================================

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Trainers management
    Route::get('/trainers', [TrainerController::class, 'index']);
    Route::get('/trainers/{id}', [TrainerController::class, 'show']);

    // Trainer invitations
    Route::get('/invitations', [TrainerInvitationController::class, 'index']);
    Route::post('/invitations', [TrainerInvitationController::class, 'store']);
    Route::delete('/invitations/{id}', [TrainerInvitationController::class, 'destroy']);
    Route::post('/invitations/{id}/resend', [TrainerInvitationController::class, 'resend']);
});
