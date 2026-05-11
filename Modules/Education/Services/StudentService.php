<?php

namespace Modules\Education\Services;

use Modules\Education\Models\Halaqa;
use Modules\Education\Models\Student;

class StudentService
{
    // Modules/Education/Services/StudentService.php

    public function list()
    {
        $user = auth()->user();
        $status = request()->query('status');

        $query = Student::query()->with(['mosque', 'parent']);

        if ($user->isSupervisor()) {
            $query->where('mosque_id', $user->mosque_id);
        }
        elseif ($user->isTeacher()) {
            $query->whereHas('halaqats', function ($q) use ($user) {
                $q->where('teacher_id', $user->id);
            });
        }
        elseif ($user->isParent()) {
            $query->where('parent_id', $user->id);
        }

        $query->when($status, function ($q) use ($status) {
            return $q->where('status', $status);
        });

        return $query->latest()->paginate(10);
    }

    public function create(array $data)
    {
        $data['parent_id'] = auth()->id();
        $data['status'] = 'pending';

        $student = Student::create($data);

        return $student->load(['mosque', 'parent']);
    }

    public function find($id)
    {
        $user = auth()->user();

        $query = Student::with(['mosque', 'parent', 'halaqats'])
            ->withCount(['attendances as total_absent' => function($q) {
                $q->whereIn('status', ['absent', 'absent_with_excuse']);
            }])
            ->withCount(['attendances as total_present' => function($q) {
                $q->where('status', 'present');
            }]);

        if ($user->isSupervisor()) {
            $query->where('mosque_id', $user->mosque_id);
        }
        elseif ($user->isTeacher()) {
            $query->whereHas('halaqats', function ($q) use ($user) {
                $q->where('teacher_id', $user->id);
            });
        }
        elseif ($user->isParent()) {
            $query->where('parent_id', $user->id);
        }

        $student = $query->findOrFail($id);

        $student->last_presence = $student->attendances()
            ->where('status', 'present')
            ->latest('date')
            ->first()?->date;

        return $student;
    }

    public function update($id, array $data)
    {
        $student = Student::findOrFail($id);
        $student->update($data);

        return $student;
    }

    public function delete($id)
    {
        Student::findOrFail($id)->delete();
    }



    public function approve($id)
    {
        $user = auth()->user();
        $student = Student::where('mosque_id', $user->mosque_id)->findOrFail($id);

        if ($student->status === 'active') {
            return ['error' => true, 'message' => __('messages.student_already_active')];
        }

        if ($student->status === 'rejected') {
            return ['error' => true, 'message' => __('messages.cannot_approve_rejected')];
        }


        $student->update(['status' => 'active']);
        return ['error' => false, 'data' => $student->load(['mosque', 'parent'])];
    }

    public function reject($id)
    {
        $user = auth()->user();
        $student = Student::where('mosque_id', $user->mosque_id)->findOrFail($id);

        if ($student->status === 'active') {
            return ['error' => true, 'message' => __('messages.cannot_reject_active')];
        }

        if ($student->status === 'rejected') {
            return ['error' => true, 'message' => __('messages.student_already_rejected')];
        }

        $student->update(['status' => 'rejected']);
        return ['error' => false, 'data' => $student->load(['mosque', 'parent'])];
    }

    public function transferHalaqa($studentId, array $data)
    {
        $user = auth()->user();
        $student = Student::where('mosque_id', $user->mosque_id)->findOrFail($studentId);

        $oldHalaqaId = $data['from_halaqa_id'];
        $newHalaqaId = $data['to_halaqa_id'];

        $newHalaqa = Halaqa::where('id', $newHalaqaId)
            ->where('mosque_id', $user->mosque_id)
            ->first();

        if (!$newHalaqa) {
            return ['error' => true, 'message' => __('messages.target_halaqa_invalid')];
        }

        // 2. التحقق من الحلقة القديمة: هل الطالب موجود فيها فعلاً؟
        $isInOldHalaqa = $student->halaqats()->where('halaqats.id', $oldHalaqaId)->exists();

        if (!$isInOldHalaqa) {
            return [
                'error' => true,
                'message' => __('messages.student_not_in_old_halaqa')
            ];
        }

        // 3. التنفيذ الآمن:
        // الآن نحن متأكدون أنه في القديمة، فنقوم بحذفه منها
        $student->halaqats()->detach($oldHalaqaId);

        // ثم نضيفه للجديدة (نستخدم sync لضمان عدم التكرار حتى لو ضغط المشرف مرتين)
        $student->halaqats()->syncWithoutDetaching([
            $newHalaqaId => [
                'joined_at' => now(),
                'status' => 'active'
            ]
        ]);

        return [
            'error' => false,
            'message' => __('messages.transfer_success', ['name' => $newHalaqa->name]),
            'data' => $student->load('halaqats')
        ];
    }

}
