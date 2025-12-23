<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvailabilitySchedule extends Model
{
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'is_default',
        'timezone',
        'schedule',
        'date_overrides',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_default' => 'boolean',
        'schedule' => 'array',
        'date_overrides' => 'array',
    ];

    /**
     * Get the user that owns the availability schedule.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the event types using this schedule.
     * Note: This relationship should be defined when EventType model is created.
     */
    // public function eventTypes(): HasMany
    // {
    //     return $this->hasMany(EventType::class, 'availability_schedule_id');
    // }
}
