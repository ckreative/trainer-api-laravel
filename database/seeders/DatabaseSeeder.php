<?php

namespace Database\Seeders;

use App\Models\AvailabilitySchedule;
use App\Models\Booking;
use App\Models\EventType;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test user
        $user = User::factory()->create([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'first_name' => 'Test',
            'last_name' => 'User',
        ]);

        // Create availability schedule
        $availabilitySchedule = AvailabilitySchedule::create([
            'user_id' => $user->id,
            'name' => 'Working Hours',
            'is_default' => true,
            'timezone' => 'America/New_York',
            'schedule' => [
                ['day' => 'Monday', 'enabled' => true, 'slots' => [['start' => '09:00', 'end' => '17:00']]],
                ['day' => 'Tuesday', 'enabled' => true, 'slots' => [['start' => '09:00', 'end' => '17:00']]],
                ['day' => 'Wednesday', 'enabled' => true, 'slots' => [['start' => '09:00', 'end' => '17:00']]],
                ['day' => 'Thursday', 'enabled' => true, 'slots' => [['start' => '09:00', 'end' => '17:00']]],
                ['day' => 'Friday', 'enabled' => true, 'slots' => [['start' => '09:00', 'end' => '17:00']]],
                ['day' => 'Saturday', 'enabled' => false, 'slots' => []],
                ['day' => 'Sunday', 'enabled' => false, 'slots' => []],
            ],
        ]);

        // Create event types
        $eventType30Min = EventType::create([
            'user_id' => $user->id,
            'title' => '30 Minute Meeting',
            'url' => '30min',
            'description' => 'A quick 30-minute meeting to discuss any topic.',
            'duration' => 30,
            'enabled' => true,
            'location' => 'google_meet',
            'availability_schedule_id' => $availabilitySchedule->id,
        ]);

        $eventType60Min = EventType::create([
            'user_id' => $user->id,
            'title' => 'Discovery Call',
            'url' => 'discovery',
            'description' => 'A 60-minute discovery call to understand your needs.',
            'duration' => 60,
            'enabled' => true,
            'location' => 'google_meet',
            'availability_schedule_id' => $availabilitySchedule->id,
        ]);

        $eventTypeQuickSync = EventType::create([
            'user_id' => $user->id,
            'title' => 'Quick Sync',
            'url' => 'quick-sync',
            'description' => 'A brief 15-minute sync call.',
            'duration' => 15,
            'enabled' => true,
            'location' => 'phone',
            'availability_schedule_id' => $availabilitySchedule->id,
        ]);

        // Create bookings with various statuses
        $attendees = [
            ['name' => 'Sarah Chen', 'email' => 'sarah.chen@example.com'],
            ['name' => 'Michael Rodriguez', 'email' => 'michael.rodriguez@example.com'],
            ['name' => 'Emma Watson', 'email' => 'emma.watson@example.com'],
            ['name' => 'James Park', 'email' => 'james.park@example.com'],
            ['name' => 'Lisa Johnson', 'email' => 'lisa.johnson@example.com'],
            ['name' => 'David Kim', 'email' => 'david.kim@example.com'],
            ['name' => 'Rachel Green', 'email' => 'rachel.green@example.com'],
            ['name' => 'Alex Turner', 'email' => 'alex.turner@example.com'],
        ];

        // Upcoming bookings (4)
        Booking::create([
            'user_id' => $user->id,
            'event_type_id' => $eventType30Min->id,
            'title' => '30 Minute Meeting',
            'start_time' => now()->addDays(1)->setTime(10, 0),
            'end_time' => now()->addDays(1)->setTime(10, 30),
            'status' => 'upcoming',
            'attendee_name' => $attendees[0]['name'],
            'attendee_email' => $attendees[0]['email'],
            'location' => 'google_meet',
            'meeting_url' => 'https://meet.google.com/abc-defg-hij',
            'timezone' => 'America/New_York',
        ]);

        Booking::create([
            'user_id' => $user->id,
            'event_type_id' => $eventType60Min->id,
            'title' => 'Discovery Call',
            'start_time' => now()->addDays(2)->setTime(14, 0),
            'end_time' => now()->addDays(2)->setTime(15, 0),
            'status' => 'upcoming',
            'attendee_name' => $attendees[1]['name'],
            'attendee_email' => $attendees[1]['email'],
            'location' => 'google_meet',
            'meeting_url' => 'https://meet.google.com/klm-nopq-rst',
            'timezone' => 'America/New_York',
        ]);

        Booking::create([
            'user_id' => $user->id,
            'event_type_id' => $eventTypeQuickSync->id,
            'title' => 'Quick Sync',
            'start_time' => now()->addDays(3)->setTime(11, 30),
            'end_time' => now()->addDays(3)->setTime(11, 45),
            'status' => 'upcoming',
            'attendee_name' => $attendees[2]['name'],
            'attendee_email' => $attendees[2]['email'],
            'location' => 'phone',
            'timezone' => 'America/New_York',
        ]);

        Booking::create([
            'user_id' => $user->id,
            'event_type_id' => $eventType60Min->id,
            'title' => 'Strategy Session',
            'start_time' => now()->addDays(4)->setTime(15, 30),
            'end_time' => now()->addDays(4)->setTime(16, 30),
            'status' => 'upcoming',
            'attendee_name' => $attendees[3]['name'],
            'attendee_email' => $attendees[3]['email'],
            'location' => 'google_meet',
            'meeting_url' => 'https://meet.google.com/uvw-xyza-bcd',
            'timezone' => 'America/New_York',
        ]);

        // Unconfirmed bookings (2)
        Booking::create([
            'user_id' => $user->id,
            'event_type_id' => $eventType30Min->id,
            'title' => '30 Minute Meeting',
            'start_time' => now()->addDays(5)->setTime(9, 0),
            'end_time' => now()->addDays(5)->setTime(9, 30),
            'status' => 'unconfirmed',
            'attendee_name' => $attendees[4]['name'],
            'attendee_email' => $attendees[4]['email'],
            'location' => 'google_meet',
            'meeting_url' => 'https://meet.google.com/efg-hijk-lmn',
            'timezone' => 'America/New_York',
        ]);

        Booking::create([
            'user_id' => $user->id,
            'event_type_id' => $eventType60Min->id,
            'title' => 'Consultation',
            'start_time' => now()->addDays(6)->setTime(13, 0),
            'end_time' => now()->addDays(6)->setTime(14, 0),
            'status' => 'unconfirmed',
            'attendee_name' => $attendees[5]['name'],
            'attendee_email' => $attendees[5]['email'],
            'location' => 'google_meet',
            'meeting_url' => 'https://meet.google.com/opq-rstu-vwx',
            'timezone' => 'America/New_York',
        ]);

        // Recurring booking (1)
        Booking::create([
            'user_id' => $user->id,
            'event_type_id' => $eventType30Min->id,
            'title' => 'Weekly Check-in',
            'start_time' => now()->addDays(7)->setTime(10, 0),
            'end_time' => now()->addDays(7)->setTime(10, 30),
            'status' => 'recurring',
            'attendee_name' => $attendees[6]['name'],
            'attendee_email' => $attendees[6]['email'],
            'location' => 'google_meet',
            'meeting_url' => 'https://meet.google.com/yza-bcde-fgh',
            'timezone' => 'America/New_York',
            'is_recurring' => true,
            'recurrence_rule' => 'FREQ=WEEKLY;COUNT=10',
        ]);

        // Past bookings (5)
        Booking::create([
            'user_id' => $user->id,
            'event_type_id' => $eventType30Min->id,
            'title' => '30 Minute Meeting',
            'start_time' => now()->subDays(2)->setTime(10, 0),
            'end_time' => now()->subDays(2)->setTime(10, 30),
            'status' => 'past',
            'attendee_name' => $attendees[0]['name'],
            'attendee_email' => $attendees[0]['email'],
            'location' => 'google_meet',
            'meeting_url' => 'https://meet.google.com/ijk-lmno-pqr',
            'timezone' => 'America/New_York',
        ]);

        Booking::create([
            'user_id' => $user->id,
            'event_type_id' => $eventType60Min->id,
            'title' => 'Discovery Call',
            'start_time' => now()->subDays(3)->setTime(14, 0),
            'end_time' => now()->subDays(3)->setTime(15, 0),
            'status' => 'past',
            'attendee_name' => $attendees[1]['name'],
            'attendee_email' => $attendees[1]['email'],
            'location' => 'google_meet',
            'meeting_url' => 'https://meet.google.com/stu-vwxy-zab',
            'timezone' => 'America/New_York',
        ]);

        Booking::create([
            'user_id' => $user->id,
            'event_type_id' => $eventTypeQuickSync->id,
            'title' => 'Quick Sync',
            'start_time' => now()->subDays(4)->setTime(11, 30),
            'end_time' => now()->subDays(4)->setTime(11, 45),
            'status' => 'past',
            'attendee_name' => $attendees[2]['name'],
            'attendee_email' => $attendees[2]['email'],
            'location' => 'phone',
            'timezone' => 'America/New_York',
        ]);

        Booking::create([
            'user_id' => $user->id,
            'event_type_id' => $eventType30Min->id,
            'title' => 'Follow-up Meeting',
            'start_time' => now()->subDays(5)->setTime(9, 0),
            'end_time' => now()->subDays(5)->setTime(9, 30),
            'status' => 'past',
            'attendee_name' => $attendees[3]['name'],
            'attendee_email' => $attendees[3]['email'],
            'location' => 'google_meet',
            'meeting_url' => 'https://meet.google.com/cde-fghi-jkl',
            'timezone' => 'America/New_York',
        ]);

        Booking::create([
            'user_id' => $user->id,
            'event_type_id' => $eventType60Min->id,
            'title' => 'Project Review',
            'start_time' => now()->subDays(7)->setTime(16, 0),
            'end_time' => now()->subDays(7)->setTime(17, 0),
            'status' => 'past',
            'attendee_name' => $attendees[7]['name'],
            'attendee_email' => $attendees[7]['email'],
            'location' => 'google_meet',
            'meeting_url' => 'https://meet.google.com/mno-pqrs-tuv',
            'timezone' => 'America/New_York',
        ]);

        // Cancelled bookings (2)
        Booking::create([
            'user_id' => $user->id,
            'event_type_id' => $eventType30Min->id,
            'title' => '30 Minute Meeting',
            'start_time' => now()->subDays(1)->setTime(11, 0),
            'end_time' => now()->subDays(1)->setTime(11, 30),
            'status' => 'cancelled',
            'attendee_name' => $attendees[4]['name'],
            'attendee_email' => $attendees[4]['email'],
            'location' => 'google_meet',
            'meeting_url' => 'https://meet.google.com/wxy-zabc-def',
            'timezone' => 'America/New_York',
        ]);

        Booking::create([
            'user_id' => $user->id,
            'event_type_id' => $eventType60Min->id,
            'title' => 'Consultation',
            'start_time' => now()->addDays(1)->setTime(15, 0),
            'end_time' => now()->addDays(1)->setTime(16, 0),
            'status' => 'cancelled',
            'attendee_name' => $attendees[5]['name'],
            'attendee_email' => $attendees[5]['email'],
            'location' => 'google_meet',
            'meeting_url' => 'https://meet.google.com/ghi-jklm-nop',
            'timezone' => 'America/New_York',
        ]);
    }
}
