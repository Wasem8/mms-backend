<?php

namespace Modules\Invitation\Actions;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Modules\Invitation\Models\Invitation;
use Modules\Invitation\Notifications\InvitationNotification;
use Modules\User\Models\User;

class SendInvitationAction
{
    public function execute(User $user, string $email, string $role, int $mosqueId): Invitation
    {
        // 1. تحديد اسم الصلاحية المطلوبة ديناميكياً (مثال: invite_mosque_manager)
        $permissionName = 'invite_' . $role;

        // فحص الصلاحية بداخل نظام الأدوار الحالي لديك
        if (!$user->hasPermission($permissionName)) {
            throw ValidationException::withMessages([
                'role' => 'غير مصرح لك بإرسال دعوة لهذا الدور الوظيفي.'
            ]);
        }

        // 2. تطبيق التراتبية بدقة ومنع التلاعب عبر الـ API
        if ($user->hasRole('super_admin') && $role !== 'mosque_manager') {
            throw ValidationException::withMessages([
                'role' => 'بصفتك مديراً للمنطقة (Super Admin)، يمكنك فقط دعوة مدير مسجد (Mosque Manager).'
            ]);
        }

        if ($user->hasRole('mosque_manager') && !in_array($role, ['halaqa_supervisor', 'teacher'])) {
            throw ValidationException::withMessages([
                'role' => 'بصفتك مديراً للمسجد، يمكنك فقط دعوة مدير حلقات أو معلم.'
            ]);
        }

        if ($user->hasRole('halaqa_supervisor') && $role !== 'teacher') {
            throw ValidationException::withMessages([
                'role' => 'بصفتك مديراً للحلقات، يمكنك فقط دعوة المعلمين (Teacher).'
            ]);
        }

        // 3. التحقق من أن الإيميل غير مسجل مسبقاً كمستخدم نشط
        if (User::where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => 'هذا البريد الإلكتروني مسجل بالفعل كمستخدم في النظام.'
            ]);
        }

        // 4. التحقق من عدم وجود دعوة معلقة ونشطة لنفس الإيميل
        $existingInvitation = Invitation::where('email', $email)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->exists();

        if ($existingInvitation) {
            throw ValidationException::withMessages([
                'email' => 'هناك دعوة نشطة معلقة أرسلت بالفعل لهذا البريد الإلكتروني.'
            ]);
        }

        // 🔥 5. قانون الحظر الصارم (مدير واحد ومشرف واحد فقط للمسجد الواحد)
        if (in_array($role, ['mosque_manager', 'halaqa_supervisor'])) {

            // أ. فحص إذا كان هناك مستخدم حقيقي في قاعدة البيانات يشغل هذا الدور في نفس المسجد
            $hasActiveUser = User::where('mosque_id', $mosqueId)
                ->whereHas('roles', function($query) use ($role) {
                    $query->where('name', $role);
                })->exists();

            if ($hasActiveUser) {
                $roleTitle = $role === 'mosque_manager' ? 'مدير مسجد' : 'مشرف حلقات';
                throw ValidationException::withMessages([
                    'role' => "لا يمكن إرسال الدعوة. هذا المسجد يمتلك ($roleTitle) نشط بالفعل."
                ]);
            }

            // ب. فحص إذا كان هناك دعوة سابقة معلقة لم تنتهِ صلاحيتها لنفس الدور والمسجد
            $hasPendingInvitation = Invitation::where('mosque_id', $mosqueId)
                ->where('role', $role)
                ->whereNull('accepted_at')
                ->where('expires_at', '>', now())
                ->exists();

            if ($hasPendingInvitation) {
                $roleTitle = $role === 'mosque_manager' ? 'مدير مسجد' : 'مشرف حلقات';
                throw ValidationException::withMessages([
                    'role' => "هناك دعوة معلقة قيد الانتظار لمنصب ($roleTitle) أرسلت مسبقاً لهذا المسجد."
                ]);
            }
        }

        // 6. إنشاء الدعوة وربطها بالـ mosque_id الفعلي
        $invitation = Invitation::create([
            'email'      => $email,
            'role'       => $role,
            'created_by' => $user->id,
            'token'      => Str::random(40),
            'mosque_id'  => $mosqueId,
            'expires_at' => now()->addDays(7),
        ]);

        // 7. إرسال الإشعار بالقالب الأخضر والشعار المخصص
        Notification::route('mail', $email)
            ->notify(new InvitationNotification($invitation));

        return $invitation;
    }
}
