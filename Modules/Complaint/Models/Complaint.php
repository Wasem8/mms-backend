<?php

namespace Modules\Complaint\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\User\Models\User;
use Modules\Mosque\Models\Mosque;
use Modules\Complaint\Models\ComplaintStatusLog;
// use Modules\Complaint\Database\Factories\ComplaintFactory;

class Complaint extends Model
{
    use HasFactory;

    protected $table = 'complaints';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'description',
        'mosque_id',
        'user_id',
        'complaint_number',
        'status',
        'complaint_type',
        'priority',
        'email',
        'is_anonymous'
    ];
    // protected static function newFactory(): ComplaintFactory
    // {
    //     // return ComplaintFactory::new();
    // }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function mosque() {
        return $this->belongsTo(Mosque::class);
    }
    public function statusLogs() {
        return $this->hasMany(ComplaintStatusLog::class);
    }

    public function files() {
        return $this->hasMany(ComplaintFile::class);
    }

}
