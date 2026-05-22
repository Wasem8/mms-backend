<?php

namespace Modules\Complaint\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'reference_number',
        'mosque_id',
        'region_manager_id',
        'title',
        'description',
        'category',
        'urgency',
        'status',
        'rejection_reason',
        'attachments',
    ];

    protected $casts = [
        'attachments' => 'array',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function mosque(): BelongsTo
    {
        return $this->belongsTo(Mosque::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(MaintenanceRequestStatusLog::class)
            ->orderBy('changed_at');
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
