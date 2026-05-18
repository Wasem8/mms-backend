<?php

namespace Modules\Common\Listeners;

use Modules\Education\Events\EvaluationUpdated;
use Modules\Education\Events\StudentEvaluated;
use Modules\Common\Services\NotificationService;

class SendEvaluationNotification
{
    /**
     * @param object $event
     */
    public function handle(object $event)
    {
        $evaluation = $event->evaluation;
        $student = $evaluation->student;
        $parent = $student->parent;

        $isUpdate = $event instanceof EvaluationUpdated;

        $title = $isUpdate
            ? "تحديث في تقييم ابنك: " . $student->first_name
            : "تقييم جديد لابنك: " . $student->first_name;

        $body = $isUpdate
            ? "تم تعديل تقييم الطالب {$student->first_name} في {$evaluation->surah_name}"
            : "تم إضافة تقييم جديد لـ {$student->first_name} في {$evaluation->surah_name} بدرجة {$evaluation->score}";

        $type = $isUpdate ? 'evaluation_updated' : 'evaluation_added';

        $extraData = [
            'evaluation_id' => (string) $evaluation->id,
            'student_id'    => (string) $student->id,
        ];

        if ($parent) {
            $notificationService = new NotificationService();
            $notificationService->notify(
                $parent,
                $title,
                $body,
                $type,
                $extraData
            );
        }
    }
}
