<?php

namespace Modules\Volunteer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\User\Models\User;
// use Modules\Volunteer\Database\Factories\VolunteerLogFactory;

class VolunteerLog extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'volunteer_logs';

    protected $fillable = [
        'volunteer_id',
        'opportunity_id',
        'logged_hours',
        'manager_evaluation',
        'notes',
    ];

    protected $casts = [
        'logged_hours' => 'decimal:2',
    ];

    public function volunteer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'volunteer_id');
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(VolunteerOpportunity::class, 'opportunity_id');
    }
    // protected static function newFactory(): VolunteerLogFactory
    // {
    //     // return VolunteerLogFactory::new();
    // }
}
