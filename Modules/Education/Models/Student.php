<?php

namespace Modules\Education\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Mosque\Models\Mosque;
use Modules\User\Models\User;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'first_name',
        'last_name',
        'mosque_id',
        'halaqa_id',
        'date_of_birth',
        'gender',
        'status',
    ];

    public function halaqats()
    {
        return $this->belongsTo(Halaqa::class, 'halaqa_id');
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function mosque()
    {
        return $this->belongsTo(Mosque::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function scopeForUser($query, $user)
    {
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        return match (true) {
            $user->isAreaManager()   => $query,
            $user->isMosqueManager() => $query->where('mosque_id', $user->mosque_id),
            $user->isSupervisor()    => $query->where('mosque_id', $user->mosque_id),
            $user->isTeacher()       => $query->whereHas('halaqats', fn($q) => $q->where('teacher_id', $user->id)),
            $user->isParent()        => $query->where('parent_id', $user->id),
            default                  => $query->whereRaw('1 = 0'),
        };
    }


    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    protected static function newFactory()
    {
        return \Modules\Education\Database\Factories\StudentFactory::new();
    }
}
