<?php

namespace Modules\Education\Services;

use Illuminate\Support\Facades\DB;
use Modules\User\Models\User;

class TeacherService
{

    public function getTeachersList()
    {
        $user = auth()->user();

        $query = User::role('teacher')
            ->withCount('halaqats');


        match (true) {

            $user->isAreaManager() => null,
            $user->isMosqueManager() || $user->isSupervisor()
            => $query->where('mosque_id', $user->mosque_id),
            $user->isTeacher() => $query->where('id', $user->id),
            default => $query->whereRaw('1 = 0'),
        };

        return $query->latest()->get(['id', 'name', 'phone', 'status', 'created_at']);
    }


    public function getTeacherDetails($teacherId)
    {
        $user = auth()->user();

        $query = User::role('teacher')
            ->with(['halaqats' => function($query) {
                $query->withCount([
                    'students',
                    'attendances as total_absent_count' => fn($q) => $q->whereIn('status', ['absent', 'absent_with_excuse']),
                    'attendances as total_present_count' => fn($q) => $q->where('status', 'present')
                ]);
            }]);

        if (!$user->isAreaManager()) {
            $query->where('mosque_id', $user->mosque_id);
        }

        return $query->findOrFail($teacherId);
    }
}
