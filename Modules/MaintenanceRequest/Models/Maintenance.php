<?php

namespace Modules\MaintenanceRequest\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

// use Modules\MaintenanceRequest\Database\Factories\MaintenanceFactory;

class Maintenance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */


    protected $table = 'maintenances';
    protected $fillable = ['maintenance_number','mosque_id','title','description','category','priority','status','requested_by','scheduled_at','completed_at','notes'];


    public function files(): HasMany
    {
        return $this->hasMany(MaintenanceFile::class, 'maintenance_id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(MaintenanceStatusLog::class, 'maintenance_id')
            ->latest();
    }

    public function mosque()
    {
        return $this->belongsTo(\Modules\Mosque\Models\Mosque::class, 'mosque_id');
    }

    // protected static function newFactory(): MaintenanceFactory
    // {
    //     // return MaintenanceFactory::new();
    // }
}
