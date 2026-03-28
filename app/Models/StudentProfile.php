<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentProfile extends Model
{
    protected $fillable = [
        'user_id', 'age', 'grade', 'parent_phone',
        'address', 'current_hifz', 'enrollment_date', 'is_active'
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
