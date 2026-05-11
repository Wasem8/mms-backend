<?php

namespace Modules\Education\Actions;

use Modules\Education\Models\AttendanceExcuse;
use Modules\Education\Models\Attendance;
use Illuminate\Support\Facades\DB;

class ProcessExcuseAction
{
    public function execute(AttendanceExcuse $excuse, array $data): AttendanceExcuse
    {
        return DB::transaction(function () use ($excuse, $data) {
            $excuse->update([
                'status' => $data['status'],
                'admin_comment' => $data['admin_comment'] ?? null,
            ]);

            if ($data['status'] === 'accepted') {
                $this->updateAttendanceRecord($excuse);
            }

            return $excuse;
        });
    }

    protected function updateAttendanceRecord(AttendanceExcuse $excuse)
    {
        Attendance::updateOrCreate(
            [
                'student_id' => $excuse->student_id,
                'halaqa_id'  => $excuse->halaqa_id,
                'date'       => $excuse->absence_date,
            ],
            [
                'status' => 'absent_with_excuse',
                'notes'  => "عذر مقبول: " . $excuse->reason
            ]
        );
    }
}
