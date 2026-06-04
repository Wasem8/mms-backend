<?php

namespace Modules\Volunteer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\User\Models\User;

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

    // protected static function newFactory(): VolunteerApplicationFactory
    // {
    //     // return VolunteerApplicationFactory::new();
    // }
}
