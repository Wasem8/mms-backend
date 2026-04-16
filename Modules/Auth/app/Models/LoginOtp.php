<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LoginOtp extends Model
{
    protected $table = 'login_otps';

    protected $fillable = [
        'email',
        'otp',
        'attempts',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'attempts'   => 'integer',
    ];

    public function isExpired(): bool
    {
        return Carbon::now()->isAfter($this->expires_at);
    }

    public function isMaxAttemptsReached(): bool
    {
        // نفس حد المحاولات كتسجيل الدخول (3)
        return $this->attempts >= 3;
    }

    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }
}
