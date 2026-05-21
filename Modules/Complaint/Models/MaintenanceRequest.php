<?php

namespace Modules\Complaint\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Mosque\Models\Mosque;
use Modules\User\Models\User;

// use Modules\Complaint\Database\Factories\MaintenanceRequestFactory;

class MaintenanceRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */

    protected $fillable = [
        'mosque_id',
        'region_manager_id',
        'title',
        'description',
        'category',
        'urgency',
        'status',
        'rejection_reason',
        'attachments',
        'reference_number',
    ];

    protected $casts = [

        'attachments' => 'array',
    ];


    public function mosque(): BelongsTo
    {
        return $this->belongsTo(Mosque::class, 'mosque_id');
    }

    public function statusLogs()
    {
        return $this->hasMany(MaintenanceRequestStatusLog::class);
    }

    public function regionManager(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'region_manager_id',
        );
    }
    // protected static function newFactory(): MaintenanceRequestFactory
    // {
    //     // return MaintenanceRequestFactory::new();
    // }
}
