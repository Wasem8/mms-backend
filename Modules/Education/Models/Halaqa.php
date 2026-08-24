<?php

namespace Modules\Education\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Mosque\Models\Mosque;

class Halaqa extends Model
{
    use HasFactory;

    protected $table = 'halaqats';
    protected $fillable = [
        'name',
        'teacher_id',
        'capacity',
        'mosque_id',
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
        return $this->hasMany(Student::class, 'halaqa_id');
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

    protected static function newFactory()
    {
        return \Modules\Education\Database\Factories\HalaqaFactory::new();
    }
}
