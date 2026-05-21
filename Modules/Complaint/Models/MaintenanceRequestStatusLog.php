<?php

namespace Modules\Complaint\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\User\Models\User;

class MaintenanceRequestStatusLog extends Model
{
    use HasFactory;

    protected $table = 'maintenance_request_status_logs';

    protected $fillable = ['maintenance_request_id', 'old_status', 'new_status', 'note', 'changed_by', 'changed_at'];

    public function maintenanceRequest()
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
