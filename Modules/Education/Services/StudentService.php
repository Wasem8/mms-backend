<?php

namespace Modules\Education\Services;

use Modules\Education\Models\Halaqa;
use Modules\Education\Models\Student;

class StudentService
{

    public function list()
    {
        return Student::query()
            ->with(['mosque', 'parent'])
            ->forUser(auth()->user()) // 🔥 هنا السحر
            ->when(request('status'), fn($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(10);
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
        return Student::with(['mosque', 'parent', 'halaqats'])
            ->withCount([
                'attendances as total_absent' => fn($q) => $q->whereIn('status', ['absent', 'absent_with_excuse']),
                'attendances as total_present' => fn($q) => $q->where('status', 'present')
            ])
            ->forUser(auth()->user())
            ->findOrFail($id);
    }

    public function search(array $filters)
    {
        return Student::query()
            ->with(['mosque', 'parent', 'halaqats'])
            ->forUser(auth()->user())
            ->when(!empty($filters['query']), function ($q) use ($filters) {
                $searchTerm = $filters['query'];
                $q->where(function ($sub) use ($searchTerm) {
                    $sub->where('first_name', 'ILIKE', "%{$searchTerm}%")
                        ->orWhere('last_name', 'ILIKE', "%{$searchTerm}%")
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) ILIKE ?", ["%{$searchTerm}%"]);
                });
            })

            ->when(!empty($filters['mosque_id']), fn($q) => $q->where('mosque_id', $filters['mosque_id']))

            ->when(!empty($filters['status']), fn($q) => $q->where('status', $filters['status']))

            ->when(!empty($filters['gender']), fn($q) => $q->where('gender', $filters['gender']))

            ->when(!empty($filters['halaqa_id']), function ($q) use ($filters) {
                $q->whereHas('halaqats', function ($sub) use ($filters) {
                    $sub->where('halaqats.id', $filters['halaqa_id']);
                });
            })
            ->latest()
            ->paginate(15);
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

        $isInOldHalaqa = $student->halaqats()->where('halaqats.id', $oldHalaqaId)->exists();

        if (!$isInOldHalaqa) {
            return [
                'error' => true,
                'message' => __('messages.student_not_in_old_halaqa')
            ];
        }

        $student->halaqats()->detach($oldHalaqaId);

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
