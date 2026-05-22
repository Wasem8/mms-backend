<?php

namespace Modules\Complaint\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// use Modules\Complaint\Database\Factories\MaintenanceRequestFileFactory;

class MaintenanceRequestFile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'maintenance_request_id',
        'file',
        'file_type',
    ];

    public function maintenanceRequest(): BelongsTo
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }

    // protected static function newFactory(): MaintenanceRequestFileFactory
    // {
    //     // return MaintenanceRequestFileFactory::new();
    // }
}
