<?php

namespace Modules\Education\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Education\Models\Halaqa;
use Modules\User\Models\User;

class TeacherService
{

    public function getTeachersList(Request $request = null)
    {
        if ($request) {
            $request->validate([
                'status'   => 'nullable|string|in:active,paused,suspended',
                'search'   => 'nullable|string|max:100',
                'per_page' => 'nullable|integer|min:1|max:100',
                'page'     => 'nullable|integer|min:1',
            ]);
        }

        $user = auth()->user();

        $query = User::role('teacher')
            ->with([
                'teacherProfile' => function($q) {
                    $q->select('id', 'user_id', 'phone', 'status', 'specialization', 'notes');
                }
            ])
            // 🎯 1. إرجاع halaqats_count و students_count
            ->withCount(['halaqats'])
            ->selectSub(
                DB::table('halaqa_student')
                    ->join('halaqats','halaqats.id','=','halaqa_student.halaqa_id')
                    ->whereColumn('halaqats.teacher_id','users.id')
                    ->selectRaw('COUNT(DISTINCT halaqa_student.student_id)'),
                'students_count'
            );

        // صلاحيات الوصول
        match (true) {
            $user->isAreaManager() => null,
            $user->isMosqueManager() || $user->isSupervisor() => $query->where('mosque_id', $user->mosque_id),
            $user->isTeacher() => $query->where('id', $user->id),
            default => $query->whereRaw('1 = 0'),
        };

        if ($request) {
            // 🎯 2. الفلترة حسب الحالة (active, paused, suspended)
            if ($request->filled('status')) {
                $query->whereHas('teacherProfile', function ($q) use ($request) {
                    $q->where('status', $request->status);
                });
            }

            // 🎯 3. البحث بالاسم، الايميل، أو رقم الهاتف
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('teacherProfile', function ($qp) use ($search) {
                            $qp->where('phone', 'like', "%{$search}%");
                        });
                });
            }
        }

        // 🎯 4. دعم التصفح والـ Pagination تلقائياً
        $perPage = $request?->get('per_page', 10);

        return $query->latest('id')->paginate($perPage);
    }

    public function getTeacherDetails($teacherId)
    {
        $user = auth()->user();

        $query = User::role('teacher')
            ->with([
                'teacherProfile',
                'halaqats' => function($query) {
                    $query->withCount([
                        'students',
                        'attendances as total_absent_count' => fn($q) => $q->whereIn('status', ['absent', 'absent_with_excuse']),
                        'attendances as total_present_count' => fn($q) => $q->where('status', 'present')
                    ]);
                }
            ]);

        if (!$user->isAreaManager()) {
            $query->where('mosque_id', $user->mosque_id);
        }

        $teacher = $query->findOrFail($teacherId);

        // حساب عدد الطلاب المرتبطين بالحلقات للمعلم
        $teacher->students_count = DB::table('halaqa_student')
            ->join('halaqats','halaqats.id','=','halaqa_student.halaqa_id')
            ->where('halaqats.teacher_id', $teacher->id)
            ->distinct()
            ->count('halaqa_student.student_id');

        return $teacher;
    }


    public function updateTeacher(int $id, array $data): User
    {
        return DB::transaction(function () use ($id, $data) {

            $user = User::where('mosque_id', auth()->user()->mosque_id)
                ->findOrFail($id);

            if (!$user->hasRole('teacher')) {
                throw ValidationException::withMessages([
                    'teacher' => __('messages.user_is_not_a_teacher / الحساب المحدد ليس لمعلم.')
                ]);
            }

            if (isset($data['name'])) {
                $user->update(['name' => $data['name']]);
            }

            $user->teacherProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'phone'          => $data['phone'] ?? $user->teacherProfile?->phone,
                    'specialization' => $data['specialization'] ?? $user->teacherProfile?->specialization,
                    'status'         => $data['status'] ?? $user->teacherProfile?->status ?? 'active',
                    'notes'          => $data['notes'] ?? $user->teacherProfile?->notes,
                ]
            );


            return $user->load(['teacherProfile']);
        });
    }
}
