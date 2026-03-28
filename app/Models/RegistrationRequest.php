<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationRequest extends Model
{

    protected $fillable = [
        'name', 'email', 'password', 'age',
        'grade', 'parent_phone', 'address',
        'current_hifz', 'status', 'admin_notes'
    ];


    protected $hidden = ['password'];

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
