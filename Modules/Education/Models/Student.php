<?php

namespace Modules\Education\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Mosque\Models\Mosque;
use Modules\User\Models\User;

class Student extends Model
{
    protected $fillable = [
        'parent_id',
        'first_name',
        'last_name',
        'mosque_id',
        'date_of_birth',
        'gender',
        'status'
    ];

    public function halaqat()
    {
        return $this->belongsToMany(Halaqa::class);
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function mosque()
    {
        return $this->belongsTo(Mosque::class);
    }

    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
