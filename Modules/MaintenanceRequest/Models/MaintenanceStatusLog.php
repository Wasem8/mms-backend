<?php

namespace Modules\MaintenanceRequest\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// use Modules\MaintenanceRequest\Database\Factories\MaintenanceStatusLogFactory;

class MaintenanceStatusLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */

    protected $fillable = [
        'maintenance_id',
        'old_status',
        'new_status',
        'changed_by',
        'notes',
    ];

    public function maintenance(): BelongsTo
    {
        return $this->belongsTo(Maintenance::class, 'maintenance_id');
    }
}
