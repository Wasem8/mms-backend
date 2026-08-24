<?php

namespace Modules\Volunteer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\User\Models\User;
use Modules\Volunteer\Enums\ApplicationStatus;
use Modules\Volunteer\Enums\TaskStatus;
use Modules\Volunteer\Models\VolunteerTask;

// use Modules\Volunteer\Database\Factories\VolunteerApplicationFactory;

class VolunteerApplication extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'volunteer_applications';

    protected $fillable = [
        'opportunity_id',
        'volunteer_id',
        'status',
    ];

    protected $casts = [
        'status' => ApplicationStatus::class,
    ];

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(VolunteerOpportunity::class, 'opportunity_id');
    }

    public function volunteer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'volunteer_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(VolunteerTask::class, 'application_id');
    }

    public function getVolunteerNameAttribute(): ?string
    {
        return $this->volunteer?->name;
    }

    /**
     * True when the volunteer has at least one task on this application
     * and every one of those tasks is completed — i.e. they are eligible to log hours.
     */
    public function getAllTasksCompletedAttribute(): bool
    {
        $tasks = $this->relationLoaded('tasks')
            ? $this->tasks
            : $this->tasks()->get();

        if ($tasks->isEmpty()) {
            return false;
        }

        return $tasks->every(fn(VolunteerTask $task) => $task->status === TaskStatus::Completed);
    }

    // protected static function newFactory(): VolunteerApplicationFactory
    // {
    //     // return VolunteerApplicationFactory::new();
    // }
}
