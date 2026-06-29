<?php

namespace Modules\Common\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Common\Services\NotificationService;
use Modules\Education\Events\StudentApproved;
use Modules\Education\Events\StudentRejected;

class SendStudentStatusNotification
{
    public function __construct(protected NotificationService $notificationService) {}

    /**
     * معالجة الإشعارات بناءً على نوع الحدث الممرر
     */
    public function handle(object $event): void
    {
        $student = $event->student;
        $parent = $student->parent;


        if (!$parent) {
            return;
        }

        $studentName = trim($student->first_name . ' ' . $student->last_name);

        if ($event instanceof StudentApproved) {
            $title = '🎉 قبول طلب التسجيل';
            $body = "تمت الموافقة على قبول ابنكم ({$studentName}) في الحلقات بنجاح.";
            $type = 'student_approved';
        } elseif ($event instanceof StudentRejected) {
            $title = '⚠️ تحديث بشأن طلب التسجيل';
            $body = "نأسف لإبلاغكم بأنه تم رفض طلب تسجيل ابنكم ({$studentName}).";
            $type = 'student_rejected';
        }


        $this->notificationService->notify(
            user: $parent,
            title: $title,
            body: $body,
            type: $type,
            data: [
                'student_id'   => $student->id,
                'student_name' => $studentName
            ]
        );
    }
}
