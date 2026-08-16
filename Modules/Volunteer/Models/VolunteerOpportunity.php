<?php

namespace Modules\Volunteer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Mosque\Models\Mosque;
use Modules\Volunteer\Enums\OpportunityStatus;

// use Modules\Volunteer\Database\Factories\VolunteerOpportunityFactory;

class VolunteerOpportunity extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'volunteer_opportunities';

    protected $fillable = [
        'mosque_id',
        'title',
        'description',
        'required_volunteers',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'status' => OpportunityStatus::class,
    ];

    protected $appends = ['available_slots'];

    public function getAvailableSlotsAttribute(): int
    {
        $approved = $this->attributes['applications_count'] ?? $this->applications()->where('status', 'approved')->count();
        return $this->required_volunteers - (int) $approved;
    }

    public function mosque(): BelongsTo
    {
        return $this->belongsTo(Mosque::class, 'mosque_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(VolunteerApplication::class, 'opportunity_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(VolunteerTask::class, 'opportunity_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(VolunteerLog::class, 'opportunity_id');
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(VolunteerCertificate::class, 'opportunity_id');
    }

    // protected static function newFactory(): VolunteerOpportunityFactory
    // {
    //     // return VolunteerOpportunityFactory::new();
    // }
}
