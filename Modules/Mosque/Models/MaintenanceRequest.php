<?php

namespace Modules\Mosque\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\User\Models\User;

// use Modules\Mosque\Database\Factories\MaintenanceRequestFactory;

class MaintenanceRequest extends Model
{
    use HasFactory;

    protected $table = 'maintenance_requests';

    protected $fillable = [
        'mosque_id',
        'manager_id',
        'type',
        'description',
        'priority',
        'status',
        'admin_notes'
    ];

    public function mosque(): BelongsTo
    {
        return $this->belongsTo(Mosque::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
