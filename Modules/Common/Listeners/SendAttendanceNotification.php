<?php

namespace Modules\Common\Listeners;

use Modules\Education\Events\AttendanceRecorded;
use Modules\Common\Models\Notification as NotificationModel;
use Modules\Common\Services\NotificationService;
use Modules\Education\Models\Attendance;
use Modules\Education\Models\Student;

class SendAttendanceNotification
{
    /**
     * حد الغياب قبل إشعار ولي الأمر (غاب أكثر من ثلاث مرات).
     */
    private const ABSENCE_THRESHOLD = 3;

    public function handle(AttendanceRecorded $event)
    {
        $notificationService = new NotificationService();

        $studentIds = collect($event->records)->pluck('student_id');

        $students = Student::with('parent')->whereIn('id', $studentIds)->get()->keyBy('id');

        $halaqaId = isset($event->halaqaId) ? (string)$event->halaqaId : '';

        foreach ($event->records as $record) {
            $student = $students->get($record['student_id']);
            $parent = $student?->parent;

            if ($parent && in_array($record['status'], ['absent', 'late'])) {
                $statusText = $record['status'] == 'absent' ? 'غائباً' : 'متأخراً';
                $title = "تنبيه حضور: " . $student->first_name;
                $body = "نود إحاطتكم علماً بأن الطالب {$student->first_name} كان {$statusText} عن حلقة اليوم بتاريخ {$event->date}.";

                $type = 'attendance_absence';

                $extraData = [
                    'student_id' => (string)$student->id,
                    'halaqa_id'  => $halaqaId,
                    'date'       => (string)$event->date,
                    'status'     => (string)$record['status']
                ];

                $notificationService->notify(
                    $parent,
                    $title,
                    $body,
                    $type,
                    $extraData
                );
            }

            if ($parent && $record['status'] === 'absent') {
                $this->notifyParentOfExcessiveAbsence(
                    $notificationService,
                    $parent,
                    $student,
                    $event->date
                );
            }
        }
    }

    private function notifyParentOfExcessiveAbsence(
        NotificationService $notificationService,
        $parent,
        Student $student,
        $date
    ): void {
        $absentCount = Attendance::where('student_id', $student->id)
            ->where('status', 'absent')
            ->count();

        if ($absentCount <= self::ABSENCE_THRESHOLD) {
            return;
        }

        $alreadyNotified = NotificationModel::where('user_id', $parent->id)
            ->where('type', 'excessive_absence')
            ->whereJsonContains('data', ['student_id' => (string)$student->id])
            ->exists();

        if ($alreadyNotified) {
            return;
        }

        $notificationService->notify(
            $parent,
            'تنبيه غياب ابنك',
            "غاب ابنك {$student->full_name} أكثر من ثلاث مرات (إجمالي الغيابات: {$absentCount}).",
            'excessive_absence',
            [
                'student_id'   => (string)$student->id,
                'student_name' => $student->full_name,
                'absent_count' => (string)$absentCount,
                'date'         => (string)$date,
            ]
        );
    }
}
