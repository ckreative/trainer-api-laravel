<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventType extends Model
{
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'url',
        'description',
        'duration',
        'enabled',
        'allow_multiple_durations',
        'multiple_duration_options',
        'location',
        'custom_location',
        'before_event_buffer',
        'after_event_buffer',
        'minimum_notice',
        'time_slot_interval',
        'limit_booking_frequency',
        'booking_frequency_limit',
        'only_first_slot_per_day',
        'limit_total_duration',
        'total_duration_limit',
        'limit_upcoming_bookings',
        'upcoming_bookings_limit',
        'limit_future_bookings',
        'future_bookings_limit',
        'availability_schedule_id',
        'is_system',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'enabled' => 'boolean',
        'allow_multiple_durations' => 'boolean',
        'multiple_duration_options' => 'array',
        'limit_booking_frequency' => 'boolean',
        'booking_frequency_limit' => 'array',
        'only_first_slot_per_day' => 'boolean',
        'limit_total_duration' => 'boolean',
        'total_duration_limit' => 'array',
        'limit_upcoming_bookings' => 'boolean',
        'limit_future_bookings' => 'boolean',
        'is_system' => 'boolean',
    ];

    /**
     * Create the default "Intro Call" event type for a trainer.
     * This is a system event type that cannot be deleted.
     */
    public static function createIntroCallForTrainer(User $trainer): self
    {
        return self::create([
            'user_id' => $trainer->id,
            'title' => 'Free 15-Min Intro Call',
            'url' => 'intro-call',
            'description' => 'A quick introductory call to discuss your goals and see if we\'re a good fit.',
            'duration' => 15,
            'enabled' => true,
            'is_system' => true,
            'location' => 'google_meet',
        ]);
    }

    /**
     * Get the user that owns the event type.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the availability schedule for this event type.
     */
    public function availabilitySchedule(): BelongsTo
    {
        return $this->belongsTo(AvailabilitySchedule::class);
    }

    /**
     * Get the bookings for this event type.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
