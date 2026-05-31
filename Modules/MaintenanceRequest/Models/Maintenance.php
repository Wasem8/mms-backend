<?php

namespace Modules\MaintenanceRequest\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\MaintenanceRequest\Enums\MaintenanceCategory;
use Modules\MaintenanceRequest\Enums\MaintenancePriority;
use Modules\MaintenanceRequest\Enums\MaintenanceStatus;

// use Modules\MaintenanceRequest\Database\Factories\MaintenanceFactory;

class Maintenance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */

    protected $table = 'maintenances';
    protected $fillable = [
        'mosque_id',
        'maintenance_number',
        'title',
        'description',
        'category',
        'priority',
        'status',
        'requested_by',
        'scheduled_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'category'     => MaintenanceCategory::class,
        'priority'     => MaintenancePriority::class,
        'status'       => MaintenanceStatus::class,
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function files(): HasMany
    {
        return $this->hasMany(MaintenanceFile::class, 'maintenance_id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(MaintenanceStatusLog::class, 'maintenance_id')
            ->latest();
    }
    // protected static function newFactory(): MaintenanceFactory
    // {
    //     // return MaintenanceFactory::new();
    // }
}
