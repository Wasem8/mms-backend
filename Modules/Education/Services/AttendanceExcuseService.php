<?php

namespace Modules\Education\Services;

use Modules\Education\Models\AttendanceExcuse;

class AttendanceExcuseService
{
    public function process(int $excuseId, string $status, ?string $comment = null, int $teacherId)
    {
        $excuse = AttendanceExcuse::with('halaqa')->findOrFail($excuseId);

        if ($excuse->halaqa->teacher_id !== $teacherId) {
            throw new \Exception('Unauthorized');
        }

        if ($excuse->status !== 'pending') {
            return [
                'status' => 'conflict',
                'message' => 'already processed',
                'data' => $excuse
            ];
        }

        $excuse->update([
            'status' => $status,
            'admin_comment' => $comment,
        ]);

        return [
            'status' => 'ok',
            'message' => 'excuse updated',
            'data' => $excuse->fresh()
        ];
    }

    public function getTeacherExcuses($teacherId)
    {
        return AttendanceExcuse::with(['student:id,first_name,last_name', 'halaqa:id,name'])
            ->whereHas('halaqa', fn ($q) => $q->where('teacher_id', $teacherId))
            ->latest()
            ->paginate(15);
    }
}
