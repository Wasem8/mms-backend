<?php

namespace Modules\Education\Models;

use Illuminate\Database\Eloquent\Model;

class Halaqa extends Model
{
    protected $table = 'halaqats';
    protected $fillable = [
        'name',
        'teacher_id',
        'capacity',
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
        return $this->belongsToMany(Student::class)
            ->withPivot(['status', 'joined_at']);
    }

    public function teacher()
    {
        return $this->belongsTo(\Modules\User\Models\User::class, 'teacher_id');
    }
}
