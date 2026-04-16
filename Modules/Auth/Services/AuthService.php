<?php

namespace Modules\Auth\Services;

use App\Models\User;
use Modules\Auth\Models\LoginOtp;
use Modules\Auth\Mail\LoginOtpMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AuthService
{
    // عدد المحاولات المسموحة قبل الحجب (UC-01: 3 محاولات)
    private const MAX_ATTEMPTS        = 3;
    private const LOCKOUT_MINUTES     = 15;
    private const OTP_EXPIRES_MINUTES = 10;
    private const OTP_LENGTH          = 6;

    // ─────────────────────────────────────────
    //  تسجيل الدخول بكلمة المرور
    // ─────────────────────────────────────────
    public function loginWithPassword(string $email, string $password, string $ip): array
    {
        $this->ensureNotLockedOut($email, $ip);

        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            $this->recordFailedAttempt($email, $ip);
            $this->throwInvalidCredentials($email, $ip);
        }

        $this->ensureAccountIsActive($user);
        $this->clearFailedAttempts($email, $ip);
        $this->recordSuccessfulAttempt($email, $ip);

        return $this->buildTokenResponse($user);
    }

    // ─────────────────────────────────────────
    //  إرسال OTP (الخطوة الأولى)
    // ─────────────────────────────────────────
    public function sendOtp(string $email): void
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            return;
        }

        $this->ensureAccountIsActive($user);

        LoginOtp::where('email', $email)->delete();

        $otp = $this->generateOtp();

        LoginOtp::create([
            'email'      => $email,
            'otp'        => Hash::make($otp),
            'attempts'   => 0,
            'expires_at' => Carbon::now()->addMinutes(self::OTP_EXPIRES_MINUTES),
        ]);

        Mail::to($email)->queue(new LoginOtpMail($otp, self::OTP_EXPIRES_MINUTES));
    }

    // ─────────────────────────────────────────
    //  التحقق من OTP والدخول (الخطوة الثانية)
    // ─────────────────────────────────────────
    public function loginWithOtp(string $email, string $otp, string $ip): array
    {
        $this->ensureNotLockedOut($email, $ip);

        $record = LoginOtp::where('email', $email)
            ->latest()
            ->first();

        if (! $record) {
            throw ValidationException::withMessages([
                'otp' => ['لا يوجد رمز مرسل لهذا البريد.'],
            ]);
        }

        if ($record->isExpired()) {
            $record->delete();
            throw ValidationException::withMessages([
                'otp' => ['انتهت صلاحية الرمز، أعد الطلب.'],
            ]);
        }

        if ($record->isMaxAttemptsReached()) {
            $record->delete();
            $this->lockOut($email, $ip);
            throw ValidationException::withMessages([
                'otp' => ['تجاوزت عدد المحاولات المسموحة.'],
            ]);
        }

        if (! Hash::check($otp, $record->otp)) {
            $record->incrementAttempts();
            $remaining = self::MAX_ATTEMPTS - $record->fresh()->attempts;
            $this->recordFailedAttempt($email, $ip);

            throw ValidationException::withMessages([
                'otp' => ["رمز خاطئ. المحاولات المتبقية: {$remaining}"],
            ]);
        }

        // OTP صحيح — تنظيف وإصدار التوكن
        $record->delete();
        $this->clearFailedAttempts($email, $ip);

        $user = User::where('email', $email)->firstOrFail();
        $this->ensureAccountIsActive($user);
        $this->recordSuccessfulAttempt($email, $ip);

        return $this->buildTokenResponse($user);
    }

    // ─────────────────────────────────────────
    //  تسجيل الخروج (UC-02)
    // ─────────────────────────────────────────
    public function logout(User $user): void
    {
        // حذف التوكن الحالي فقط
        $user->currentAccessToken()->delete();
    }

    public function logoutAll(User $user): void
    {
        // حذف جميع التوكنات (كل الأجهزة)
        $user->tokens()->delete();
    }

    // ─────────────────────────────────────────
    //  Helpers خاصة
    // ─────────────────────────────────────────
    private function buildTokenResponse(User $user): array
    {
        // حذف التوكنات القديمة لضمان جلسة واحدة
        $user->tokens()->delete();

        // جلب الدور الأول للمستخدم (باعتبار أن لكل مستخدم دور أساسي واحد حالياً)
        $roleRecord = $user->roles()->first();
        $roleName = $roleRecord ? $roleRecord->name : 'guest';

        // جلب الصلاحيات الفعلية من جدول الصلاحيات المرتبط بهذا الدور
        $abilities = $this->getAbilitiesFromDatabase($roleRecord);

        $token = $user->createToken(
            name: 'wasl-app',
            abilities: $abilities,
        );

        return [
            'token'      => $token->plainTextToken,
            'token_type' => 'Bearer',
            'user'       => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $roleName,
            ],
            'redirect_to' => $this->getDashboardRoute($roleName),
        ];
    }

    /**
     * جلب أسماء الصلاحيات من جدول Permissions المرتبط بالدور
     */
    private function getAbilitiesFromDatabase($role): array
    {
        if (!$role) {
            return ['public:read'];
        }

        // إذا كان مدير منطقة، نعطيه صلاحية "النجمة" (كل شيء)
        if ($role->name === 'region_manager') {
            return ['*'];
        }

        // جلب أسماء الصلاحيات (slugs) المرتبطة بهذا الدور من قاعدة البيانات
        return $role->permissions()->pluck('name')->toArray();
    }
    /**
     * كل دور له قائمة abilities خاصة به في Sanctum
     * هذا يُمكّن التحقق الدقيق على مستوى كل endpoint
     */
    private function getAbilitiesForRole(string $role): array
    {
        return match ($role) {
            'region_manager'  => ['*'],  // صلاحيات كاملة
            'mosque_manager'  => [
                'mosque:manage', 'sermons:manage', 'donations:manage',
                'needs:manage', 'maintenance:manage', 'programs:manage',
            ],
            'supervisor'      => [
                'halaqa:manage', 'students:manage', 'teachers:evaluate',
                'transfer:manage', 'plans:manage',
            ],
            'teacher'         => [
                'attendance:record', 'evaluation:record', 'notes:send',
                'requests:submit',
            ],
            'parent'          => [
                'child:view', 'absence:submit', 'complaints:submit',
            ],
            default           => ['public:read'],
        };
    }

    private function getDashboardRoute(string $role): string
    {
        return match ($role) {
            'region_manager' => '/dashboard/region',
            'mosque_manager' => '/dashboard/mosque',
            'supervisor'     => '/dashboard/supervisor',
            'teacher'        => '/dashboard/teacher',
            'parent'         => '/dashboard/parent',
            default          => '/dashboard',
        };
    }

    // ─────────────────────────────────────────
    //  Rate limiting & lockout (Cache-based)
    // ─────────────────────────────────────────
    private function ensureNotLockedOut(string $email, string $ip): void
    {
        if (Cache::has($this->lockoutKey($email, $ip))) {
            $seconds = Cache::get($this->lockoutKey($email, $ip) . ':ttl', self::LOCKOUT_MINUTES * 60);
            throw ValidationException::withMessages([
                'email' => ["الحساب محجوب مؤقتًا. حاول بعد {$seconds} ثانية."],
            ]);
        }
    }

    private function recordFailedAttempt(string $email, string $ip): void
    {
        $key = $this->attemptsKey($email, $ip);
        $attempts = Cache::increment($key);
        Cache::put($key, $attempts, now()->addMinutes(self::LOCKOUT_MINUTES));

        DB::table('login_attempts')->insert([
            'email'        => $email,
            'ip_address'   => $ip,
            'was_successful' => false,
            'attempted_at' => now(),
        ]);

        if ($attempts >= self::MAX_ATTEMPTS) {
            $this->lockOut($email, $ip);
        }
    }

    private function lockOut(string $email, string $ip): void
    {
        $ttl = now()->addMinutes(self::LOCKOUT_MINUTES);
        Cache::put($this->lockoutKey($email, $ip), true, $ttl);
        Cache::put($this->lockoutKey($email, $ip) . ':ttl', self::LOCKOUT_MINUTES * 60, $ttl);
    }

    private function clearFailedAttempts(string $email, string $ip): void
    {
        Cache::forget($this->attemptsKey($email, $ip));
        Cache::forget($this->lockoutKey($email, $ip));
    }

    private function recordSuccessfulAttempt(string $email, string $ip): void
    {
        DB::table('login_attempts')->insert([
            'email'          => $email,
            'ip_address'     => $ip,
            'was_successful' => true,
            'attempted_at'   => now(),
        ]);
    }

    private function throwInvalidCredentials(string $email, string $ip): never
    {
        $key      = $this->attemptsKey($email, $ip);
        $attempts = Cache::get($key, 0);
        $remaining = max(0, self::MAX_ATTEMPTS - $attempts);

        throw ValidationException::withMessages([
            'email' => $remaining > 0
                ? "بيانات الدخول غير صحيحة. المحاولات المتبقية: {$remaining}"
                : 'تجاوزت عدد المحاولات. سيُفتح خيار الاستعادة.',
        ]);
    }

    private function ensureAccountIsActive(User $user): void
    {
        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['هذا الحساب معطّل. تواصل مع المسؤول.'],
            ]);
        }
    }

    private function generateOtp(): string
    {
        return str_pad(random_int(0, 999999), self::OTP_LENGTH, '0', STR_PAD_LEFT);
    }

    private function attemptsKey(string $email, string $ip): string
    {
        return 'login_attempts:' . md5($email . $ip);
    }

    private function lockoutKey(string $email, string $ip): string
    {
        return 'lockout:' . md5($email . $ip);
    }
}
