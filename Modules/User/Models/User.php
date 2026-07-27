<?php

namespace Modules\User\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authentication;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Modules\Education\Models\Halaqa;
use Modules\Education\Models\Student;
use Modules\Mosque\Models\Mosque;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authentication implements JWTSubject
{
    use  Notifiable,HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'name',
        'email',
        'phone',
        'password',
        'otp',
        'otp_expires_at',
        'email_verified_at',
        'fcm_token',
        'mosque_id',
        'status'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp',
        'otp_expires_at',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'otp_expires_at'=>'datetime',
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
            'otp' => Hash::make($otp),
            'otp_expires_at' => now()->addMinutes(10)
        ]);

        return $otp;
    }

    public function verifyOtp(string $otp): bool
    {
        if (!$this->otp || !$this->otp_expires_at) {
            return false;
        }

        return Hash::check($otp, $this->otp)
            && now()->lte($this->otp_expires_at);
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

    public function isMosqueManager()
    {
        return $this->hasRole('mosque_manager');
    }

    public function isAreaManager()
    {
        return $this->hasRole('super_admin');
    }

    public function isSupervisor(): bool
    {
        return $this->hasRole('halaqa_supervisor');
    }

    public function isParent(): bool
    {
        return $this->hasRole('parent');
    }

    public function isTeacher(): bool
    {
        return $this->hasRole('teacher');
    }

    public function isVolunteer(): bool
    {
        return $this->hasRole('volunteer');
    }

    public function scopeRole($query, $roleName)
    {
        return $query->whereHas('roles', function ($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }

    public function halaqats()
    {
        return $this->hasMany(Halaqa::class, 'teacher_id');
    }

    public function students()
    {
        return $this->hasManyThrough(
            Student::class,
            Halaqa::class,
            'teacher_id',
            'halaqa_id',
            'id',
            'id'
        );
    }

    public function teacherProfile()
    {
        return $this->hasOne(TeacherProfile::class, 'user_id');
    }

    public function assignRole($roleName): void
    {
        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            return;
        }

        if (!$this->roles()->where('role_id', $role->id)->exists()) {
            $this->roles()->attach($role->id);
        }
    }
}
