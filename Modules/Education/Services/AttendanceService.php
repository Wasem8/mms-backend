<?php

namespace Modules\Education\Services;

use Modules\Education\Models\Attendance;

class AttendanceService
{
    public function mark(array $data)
    {
        return Attendance::updateOrCreate(
            [
                'halaqa_id' => $data['halaqa_id'],
                'student_id' => $data['student_id'],
                'date' => $data['date'],
            ],
            [
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
            ]
        );
    }

    public function list(array $filters)
    {
        return Attendance::when($filters['halaqa_id'] ?? null, fn ($q, $id) =>
        $q->where('halaqa_id', $id)
        )
            ->when($filters['date'] ?? null, fn ($q, $date) =>
            $q->whereDate('date', $date)
            )
            ->get();
    }
}
