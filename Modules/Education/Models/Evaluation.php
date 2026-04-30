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
        'evaluated_at'
    ];
}
