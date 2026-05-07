<?php

namespace Modules\Education\Services;

use Illuminate\Support\Facades\DB;
use Modules\User\Models\User;

class TeacherService
{
    /**
     * جلب قائمة المعلمين (بيانات خفيفة)
     */
    public function getMosqueTeachersList($mosqueId)
    {
        return User::role('teacher')
            ->where('mosque_id', $mosqueId)
            ->withCount('halaqats')
            ->get(['id', 'name', 'phone', 'status', 'created_at']);
    }

    /**
     * جلب تفاصيل معلم واحد (إحصائيات عميقة)
     */
    public function getTeacherDetails($mosqueId, $teacherId)
    {
        return User::role('teacher')
            ->where('mosque_id', $mosqueId)
            ->with(['halaqats' => function($query) {
                $query->withCount([
                    'students',
                    // إجمالي حالات الغياب التاريخية
                    'attendances as total_absent_count' => function($q) {
                        $q->whereIn('status', ['absent', 'absent_with_excuse']);
                    },
                    // إجمالي حالات الحضور التاريخية
                    'attendances as total_present_count' => function($q) {
                        $q->where('status', 'present');
                    }
                ]);
            }])
            ->findOrFail($teacherId);
    }
}
