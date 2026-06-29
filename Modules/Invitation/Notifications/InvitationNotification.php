<?php

namespace Modules\Invitation\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\Invitation\Models\Invitation;

class InvitationNotification extends Notification
{
    use Queueable;

    /**
     * إنشاء نسخة جديدة من الإشعار وتمرير بيانات الدعوة له.
     */
    public function __construct(public Invitation $invitation) {}

    /**
     * تحديد قنوات إرسال الإشعار (البريد الإلكتروني فقط).
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * بناء وهيكلة رسالة البريد الإلكتروني المرسلة للمستخدم.
     */
    public function toMail($notifiable): MailMessage
    {
        // بناء الرابط المباشر لصفحة الفورم الخضراء بشكل مضمون
        $acceptUrl = url('/invitations/accept?token=' . $this->invitation->token);

        // رابط الشعار المعتمد الخاص بمنصتكم
        $logoUrl = 'https://koihzqfwzvnrcrrtpnyg.supabase.co/storage/v1/object/public/images/logo.png';

        // تحميل علاقة المسجد للتأكد من توفر البيانات في الـ view
        // ملاحظة: تأكد من وجود علاقة باسم mosque() داخل موديل Invitation
        $this->invitation->loadMissing('mosque');

        // استخدام view مخصص بداخل MailMessage يمنحنا تحكماً كاملاً بالألوان الخضراء والشعار المخصص
        return (new MailMessage)
            ->subject('دعوة للانضمام إلى منصة وَصْل التعليمية 🌟')
            ->view('invitation::invite', [
                'invitation' => $this->invitation,
                'acceptUrl'  => $acceptUrl,
                'logoUrl'    => $logoUrl
            ]);
    }
}
