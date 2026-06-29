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
        'dimensions',
        'voice_note_id',
        'evaluated_at'
    ];

    protected $casts = [
        'dimensions' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function halaqa()
    {
        return $this->belongsTo(Halaqa::class);
    }

    public function voiceNote()
    {
        return $this->belongsTo(MediaUpload::class, 'voice_note_id');
    }
}
