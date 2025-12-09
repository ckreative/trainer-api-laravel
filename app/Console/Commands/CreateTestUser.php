<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateTestUser extends Command
{
    protected $signature = 'user:create-test';
    protected $description = 'Create a test user for development/testing';

    public function handle(): int
    {
        $user = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'username' => 'testuser',
                'first_name' => 'Test',
                'last_name' => 'User',
                'password' => Hash::make('password'),
                'email_verified' => true,
            ]
        );

        $this->info("Test user created/updated: {$user->email}");
        return 0;
    }
}
