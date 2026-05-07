<?php

namespace Modules\Education\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Mosque\Models\Mosque;

class Halaqa extends Model
{
    protected $table = 'halaqats';
    protected $fillable = [
        'name',
        'teacher_id',
        'capacity',
        'mosque_id',
        'level',
        'schedule_days',
        'start_time',
        'end_time',
        'status'
    ];

    protected $casts = [
        'schedule_days' => 'array'
    ];

    public function students()
    {
        return $this->belongsToMany(Student::class, 'halaqa_student', 'halaqa_id', 'student_id')
            ->withPivot(['status', 'joined_at']);
    }

    public function teacher()
    {
        return $this->belongsTo(\Modules\User\Models\User::class, 'teacher_id');
    }

    public function mosque()
    {
        return $this->belongsTo(Mosque::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
