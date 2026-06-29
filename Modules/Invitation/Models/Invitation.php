<?php

namespace Modules\Invitation\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Mosque\Models\Mosque;
use Modules\User\Models\User;

class Invitation extends Model
{
    protected $fillable = [
        'email',
        'role',
        'created_by',
        'mosque_id',
        'token',
        'expires_at',
        'accepted_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isValid(): bool
    {
        return !$this->accepted_at &&
            (!$this->expires_at || now()->lessThan($this->expires_at));
    }

    public function mosque() {
        return $this->belongsTo(Mosque::class, 'mosque_id');
    }
}
