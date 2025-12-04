<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('event_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');

            // Basic fields
            $table->string('title', 100);
            $table->string('url', 100);
            $table->text('description')->nullable();
            $table->integer('duration'); // in minutes
            $table->boolean('enabled')->default(true);

            // Multiple durations
            $table->boolean('allow_multiple_durations')->default(false);
            $table->json('multiple_duration_options')->nullable();

            // Location
            $table->string('location')->nullable();
            $table->string('custom_location', 200)->nullable();

            // Buffers and notice
            $table->integer('before_event_buffer')->default(0); // in minutes
            $table->integer('after_event_buffer')->default(0); // in minutes
            $table->integer('minimum_notice')->default(120); // in minutes
            $table->integer('time_slot_interval')->nullable(); // in minutes

            // Booking frequency limits
            $table->boolean('limit_booking_frequency')->default(false);
            $table->json('booking_frequency_limit')->nullable(); // {count, period}

            // Other limits
            $table->boolean('only_first_slot_per_day')->default(false);

            // Total duration limits
            $table->boolean('limit_total_duration')->default(false);
            $table->json('total_duration_limit')->nullable(); // {duration, period}

            // Upcoming bookings limit
            $table->boolean('limit_upcoming_bookings')->default(false);
            $table->integer('upcoming_bookings_limit')->nullable();

            // Future bookings limit
            $table->boolean('limit_future_bookings')->default(false);
            $table->integer('future_bookings_limit')->nullable(); // in days

            // Availability schedule reference
            $table->uuid('availability_schedule_id')->nullable();

            $table->timestamps();

            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('availability_schedule_id')->references('id')->on('availability_schedules')->onDelete('set null');

            // Indexes
            $table->index('user_id');
            $table->index('enabled');
            $table->unique(['user_id', 'url']); // URL must be unique per user
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_types');
    }
};
