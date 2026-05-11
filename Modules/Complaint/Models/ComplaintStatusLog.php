<?php

namespace Modules\Complaint\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\User\Models\User;

// use Modules\Complaint\Database\Factories\ComplaintStatusLogFactory;

class Complaint_status_log extends Model
{
    use HasFactory;


    protected $table = 'complaint_status_logs';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['complaint_id', 'new_status', 'note', 'changed_at', 'changed_by'];

    public function complaint() {
        return $this->belongsTo(Complaint::class);
    }

    public function user() {
        return $this->belongsTo(User::class, 'changed_by');
    }

    // protected static function newFactory(): ComplaintStatusLogFactory
    // {
    //     // return ComplaintStatusLogFactory::new();
    // }
}
