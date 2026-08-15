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

        $permissionName = 'invite_' . $role;

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

        $existingInvitation = Invitation::where('email', $email)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->exists();

        if ($existingInvitation) {
            throw ValidationException::withMessages([
                'email' => 'هناك دعوة نشطة معلقة أرسلت بالفعل لهذا البريد الإلكتروني.'
            ]);
        }


        if (in_array($role, ['mosque_manager', 'halaqa_supervisor'])) {

            // 🎯 [التعديل هنا]: إضافة شرط 'status' => 'active' لفحص المستعملين النشطين فقط
            $hasActiveUser = User::where('mosque_id', $mosqueId)
                ->where('status', 'active') // 👈 يتجاهل الحسابات غير النشطة (inactive)
                ->whereHas('roles', function($query) use ($role) {
                    $query->where('name', $role);
                })->exists();

            if ($hasActiveUser) {
                $roleTitle = $role === 'mosque_manager' ? 'مدير مسجد' : 'مشرف حلقات';
                throw ValidationException::withMessages([
                    'role' => "لا يمكن إرسال الدعوة. هذا المسجد يمتلك ($roleTitle) نشط بالفعل."
                ]);
            }

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

        $invitation = Invitation::create([
            'email'      => $email,
            'role'       => $role,
            'created_by' => $user->id,
            'token'      => Str::random(40),
            'mosque_id'  => $mosqueId,
            'expires_at' => now()->addDays(7),
        ]);

        Notification::route('mail', $email)
            ->notify(new InvitationNotification($invitation));

        return $invitation;
    }

    public function resend(Invitation $invitation): Invitation
    {
        // 1️⃣ يمنع إعادة الإرسال إذا كانت الدعوة مقبولة بالفعل
        if ($invitation->accepted_at !== null) {
            throw ValidationException::withMessages([
                'invitation' => 'لا يمكن إعادة إرسال الدعوة لأن المستخدم قَبِلها بالفعل.'
            ]);
        }

        // 2️⃣ حظر تجاوز الحد الأقصى لإعادة الإرسال (مثلاً 3 مرات)
        if (($invitation->resend_count ?? 0) >= 3) {
            throw ValidationException::withMessages([
                'invitation' => 'لقد وصلت للحد الأقصى المسموح به لإعادة إرسال هذه الدعوة (3 مرات).'
            ]);
        }

        // 3️⃣ مهلة زمنية بين كل إرسال والآخر (مثلاً دقيقتان)
        if ($invitation->updated_at && $invitation->updated_at->addMinutes(2)->isFuture()) {
            $secondsLeft = now()->diffInSeconds($invitation->updated_at->addMinutes(2));
            throw ValidationException::withMessages([
                'invitation' => "يرجى الانتظار {$secondsLeft} ثانية قبل محاولة إعادة الإرسال مجدداً."
            ]);
        }

        // 🟢 تجديد البيانات وتمديد الصلاحية لـ 7 أيام إضافية وزيادة العداد
        $invitation->update([
            'token'        => Str::random(40),
            'expires_at'   => now()->addDays(7),
            'resend_count' => ($invitation->resend_count ?? 0) + 1,
        ]);

        // إعادة إرسال الإشعار عبر البريد
        Notification::route('mail', $invitation->email)
            ->notify(new InvitationNotification($invitation));

        return $invitation;
    }
}
