<?php

namespace Modules\MaintenanceRequest\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// use Modules\MaintenanceRequest\Database\Factories\MaintenanceFileFactory;

class MaintenanceFile extends Model
{
    use HasFactory;

    
    protected $fillable = [
        'maintenance_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function maintenance(): BelongsTo
    {
        return $this->belongsTo(Maintenance::class, 'maintenance_id');
    }
}
