<?php

namespace App\Observers;

use App\Enums\Role;
use App\Models\EventType;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     * Auto-create Intro Call event type for new trainers.
     */
    public function created(User $user): void
    {
        if ($user->role === Role::TRAINER) {
            $this->ensureIntroCallExists($user);
        }
    }

    /**
     * Handle the User "updated" event.
     * Auto-create Intro Call if user becomes a trainer.
     */
    public function updated(User $user): void
    {
        // Check if role changed to trainer
        if ($user->wasChanged('role') && $user->role === Role::TRAINER) {
            $this->ensureIntroCallExists($user);
        }
    }

    /**
     * Ensure the Intro Call event type exists for the trainer.
     */
    private function ensureIntroCallExists(User $trainer): void
    {
        // Check if intro call already exists
        $existingIntroCall = EventType::where('user_id', $trainer->id)
            ->where('is_system', true)
            ->first();

        if (! $existingIntroCall) {
            EventType::createIntroCallForTrainer($trainer);
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
