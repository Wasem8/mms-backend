<?php

namespace Modules\Education\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    protected $fillable = [
        'halaqa_id',
        'student_id',
        'score',
        'notes',
        'client_uuid',
        'surah_name',
        'from_ayah',
        'to_ayah',
        'evaluated_at'
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
