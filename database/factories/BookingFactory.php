<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\EventType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = fake()->dateTimeBetween('-30 days', '+30 days');
        $duration = fake()->randomElement([15, 30, 45, 60]);
        $endTime = (clone $startTime)->modify("+{$duration} minutes");

        return [
            'user_id' => User::factory(),
            'event_type_id' => EventType::factory(),
            'title' => fake()->randomElement([
                '30 Minute Meeting',
                'Discovery Call',
                'Quick Sync',
                'Strategy Session',
                'Consultation',
                'Interview',
                'Coaching Session',
                'Team Meeting',
            ]),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => fake()->randomElement(['upcoming', 'unconfirmed', 'recurring', 'past', 'cancelled']),
            'attendee_name' => fake()->name(),
            'attendee_email' => fake()->safeEmail(),
            'location' => fake()->randomElement(['google_meet', 'zoom', 'phone', null]),
            'meeting_url' => fake()->optional(0.7)->url(),
            'notes' => fake()->optional(0.3)->sentence(),
            'timezone' => 'America/New_York',
            'is_recurring' => false,
            'recurrence_rule' => null,
        ];
    }

    /**
     * Indicate that the booking is upcoming.
     */
    public function upcoming(): static
    {
        return $this->state(function (array $attributes) {
            $startTime = fake()->dateTimeBetween('+1 day', '+14 days');
            $duration = fake()->randomElement([30, 45, 60]);
            $endTime = (clone $startTime)->modify("+{$duration} minutes");

            return [
                'status' => 'upcoming',
                'start_time' => $startTime,
                'end_time' => $endTime,
            ];
        });
    }

    /**
     * Indicate that the booking is unconfirmed.
     */
    public function unconfirmed(): static
    {
        return $this->state(function (array $attributes) {
            $startTime = fake()->dateTimeBetween('+1 day', '+7 days');
            $duration = fake()->randomElement([30, 45, 60]);
            $endTime = (clone $startTime)->modify("+{$duration} minutes");

            return [
                'status' => 'unconfirmed',
                'start_time' => $startTime,
                'end_time' => $endTime,
            ];
        });
    }

    /**
     * Indicate that the booking is recurring.
     */
    public function recurring(): static
    {
        return $this->state(function (array $attributes) {
            $startTime = fake()->dateTimeBetween('+1 day', '+7 days');
            $duration = fake()->randomElement([30, 60]);
            $endTime = (clone $startTime)->modify("+{$duration} minutes");

            return [
                'status' => 'recurring',
                'start_time' => $startTime,
                'end_time' => $endTime,
                'is_recurring' => true,
                'recurrence_rule' => 'FREQ=WEEKLY;COUNT=10',
            ];
        });
    }

    /**
     * Indicate that the booking is in the past.
     */
    public function past(): static
    {
        return $this->state(function (array $attributes) {
            $startTime = fake()->dateTimeBetween('-30 days', '-1 day');
            $duration = fake()->randomElement([30, 45, 60]);
            $endTime = (clone $startTime)->modify("+{$duration} minutes");

            return [
                'status' => 'past',
                'start_time' => $startTime,
                'end_time' => $endTime,
            ];
        });
    }

    /**
     * Indicate that the booking is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(function (array $attributes) {
            $startTime = fake()->dateTimeBetween('-7 days', '+7 days');
            $duration = fake()->randomElement([30, 45, 60]);
            $endTime = (clone $startTime)->modify("+{$duration} minutes");

            return [
                'status' => 'cancelled',
                'start_time' => $startTime,
                'end_time' => $endTime,
            ];
        });
    }

    /**
     * Set Google Meet as the location.
     */
    public function withGoogleMeet(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'location' => 'google_meet',
                'meeting_url' => 'https://meet.google.com/' . fake()->regexify('[a-z]{3}-[a-z]{4}-[a-z]{3}'),
            ];
        });
    }
}
