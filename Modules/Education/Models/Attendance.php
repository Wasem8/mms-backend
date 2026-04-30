<?php

namespace Modules\Education\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'halaqa_id',
        'student_id',
        'date',
        'status',
        'notes'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function halaqa()
    {
        return $this->belongsTo(Halaqa::class);
    }
}
