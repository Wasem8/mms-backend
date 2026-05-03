<?php

namespace Modules\Education\Services;

use Illuminate\Validation\ValidationException;
use Modules\Education\Models\Attendance;
use Modules\Education\Models\Halaqa;

class AttendanceService
{

    public function index(array $filters = [])
    {
        $user = auth()->user();

        $query = Attendance::with([
            'student:id,first_name,last_name,parent_id',
            'halaqa:id,name,teacher_id,mosque_id'
        ]);

        // 👨‍🏫 Teacher
        if ($user->isTeacher()) {
            $query->whereHas('halaqa', function ($q) use ($user) {
                $q->where('teacher_id', $user->id);
            });
        }

        // 👨‍👩‍👧 Parent
        elseif ($user->isParent()) {
            $query->whereHas('student', function ($q) use ($user) {
                $q->where('parent_id', $user->id);
            });
        }

        // 👨‍💼 Supervisor
        elseif ($user->isSupervisor()) {
            $query->whereHas('halaqa', function ($q) use ($user) {
                $q->where('mosque_id', $user->mosque_id);
            });
        }

        // 🔍 فلترة إضافية (اختياري)
        if (!empty($filters['halaqa_id'])) {
            $query->where('halaqa_id', $filters['halaqa_id']);
        }

        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (!empty($filters['date'])) {
            $query->whereDate('date', $filters['date']);
        }

        return $query->latest()->paginate(15);
    }

    public function storeBulk(array $data)
    {
        $user = auth()->user();

        $halaqa = Halaqa::findOrFail($data['halaqa_id']);

        if ($halaqa->teacher_id !== $user->id) {

            throw ValidationException::withMessages([
                'students' => ['غير مصرح لك بتسجيل الحضور لهذه الحلقة']
            ]);
        }

        $date = $data['date'];

        $records = collect($data['attendances'])->map(function ($item) use ($data, $date) {
            return [
                'halaqa_id' => $data['halaqa_id'],
                'student_id' => $item['student_id'],
                'date' => $date,
                'status' => $item['status'],
                'notes' => $item['notes'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();

        $incomingIds = collect($data['attendances'])->pluck('student_id')->unique();

        $validIds = $halaqa->students()
            ->whereIn('students.id', $incomingIds)
            ->pluck('students.id');

        $invalidIds = $incomingIds->diff($validIds);

        if ($invalidIds->isNotEmpty()) {
            throw ValidationException::withMessages([
                'students' => ['بعض الطلاب غير تابعين لهذه الحلقة', 'invalid_ids' => $invalidIds->values()]
            ]);
        }

        Attendance::upsert(
            $records,
            ['halaqa_id', 'student_id', 'date'], // unique keys
            ['status', 'notes', 'updated_at']
        );
    }
}
