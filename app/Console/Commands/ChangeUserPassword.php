<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ChangeUserPassword extends Command
{
    protected $signature = 'user:change-password {email} {password}';
    protected $description = 'Change a user password';

    public function handle(): int
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email {$email} not found.");
            return 1;
        }

        $user->update(['password' => Hash::make($password)]);

        $this->info("Password updated for {$email}");
        return 0;
    }
}
