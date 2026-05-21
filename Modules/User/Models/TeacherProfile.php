<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\User\Database\Factories\TeacherProfileFactory;

class TeacherProfile extends Model
{
    use HasFactory;

    protected $table = 'teacher_profiles';

    protected $fillable = [
        'user_id',
        'phone',
        'specialization',
        'status',
        'notes'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
