<?php

namespace Modules\Education\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\User\Models\User;

// use Modules\Education\Database\Factories\AttendanceExcuseFactory;

class AttendanceExcuse extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'student_id',
        'halaqa_id',
        'parent_id',
        'absence_date',
        'reason',
        'status',
        'admin_comment'
    ];

    public function student() {
        return $this->belongsTo(Student::class);
    }

    public function parent() {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function halaqa() {
        return $this->belongsTo(Halaqa::class);
    }
}
