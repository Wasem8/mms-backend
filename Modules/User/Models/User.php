<?php

namespace Modules\User\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authentication;
use Illuminate\Notifications\Notifiable;
use Modules\Education\Models\Student;
use Modules\Mosque\Models\Mosque;
use Tymon\JWTAuth\Contracts\JWTSubject;


class User extends Authentication implements JWTSubject
{
    use  Notifiable,HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'otp',
        'otp_expires_at',
        'email_verified_at',
        'mosque_id',
        'status'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'role_user',
            'user_id',
            'role_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function hasRole($role)
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function hasPermission($permission)
    {
        return $this->roles()
            ->whereHas('permissions', function ($q) use ($permission) {
                $q->where('name', $permission);
            })->exists();
    }

    public function generateOtp(): string
    {
        $otp = rand(100000, 999999);

        $this->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10)
        ]);

        return $otp;
    }

    public function verifyOtp($otp): bool
    {
        if (! $this->otp || ! $this->otp_expires_at) {
            return false;
        }

        return (string)$this->otp === (string)$otp
            && now()->lessThanOrEqualTo($this->otp_expires_at);
    }

    public function clearOtp(): void
    {
        $this->update([
            'otp' => null,
            'otp_expires_at' => null
        ]);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }


    public function mosque()
    {
        return $this->belongsTo(Mosque::class);
    }

    public function children()
    {
        return $this->hasMany(Student::class, 'parent_id');
    }


    public function isSupervisor(): bool
    {
        return $this->hasRole('halaqa_supervisor');
    }

    public function isParent(): bool
    {
        return $this->hasRole('parent');
    }
}
