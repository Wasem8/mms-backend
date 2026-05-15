<?php

namespace Modules\Common\Listeners;

use Modules\Education\Events\AttendanceRecorded;
use Modules\Common\Services\NotificationService;
use Modules\Education\Models\Student;

class SendAttendanceNotification
{
    public function handle(AttendanceRecorded $event)
    {
        $notificationService = new NotificationService();

        // 1. جلب كل الـ IDs للطلاب الموجودين في السجلات
        $studentIds = collect($event->records)->pluck('student_id');

        $students = Student::with('parent')->whereIn('id', $studentIds)->get()->keyBy('id');

        foreach ($event->records as $record) {
            $student = $students->get($record['student_id']);
            $parent = $student?->parent;

            if ($parent && in_array($record['status'], ['absent', 'late'])) {
                    $statusText = $record['status'] == 'absent' ? 'غائباً' : 'متأخراً';
                    $title = "تنبيه حضور: " . $student->first_name;
                    $body = "نود إحاطتكم علماً بأن الطالب {$student->first_name} كان {$statusText} عن حلقة اليوم بتاريخ {$event->date}.";

                    $notificationService->notify(
                        $parent,
                        $title,
                        $body,
                        "attendance",
                        [
                            'student_id' => (string)$student->id,
                            'date' => $event->date,
                            'status' => $record['status']
                        ]
                    );
                }
            }
        }
}
