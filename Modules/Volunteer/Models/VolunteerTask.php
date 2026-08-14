<?php

namespace Modules\Volunteer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Volunteer\Enums\TaskStatus;
// use Modules\Volunteer\Database\Factories\VolunteerTaskFactory;

class VolunteerTask extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'volunteer_tasks';

    protected $fillable = [
        'opportunity_id',
        'application_id',
        'task_description',
        'status',
    ];

    protected $casts = [
        'status' => TaskStatus::class,
    ];

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(VolunteerOpportunity::class, 'opportunity_id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(VolunteerApplication::class, 'application_id');
    }
    // protected static function newFactory(): VolunteerTaskFactory
    // {
    //     // return VolunteerTaskFactory::new();
    // }
}
