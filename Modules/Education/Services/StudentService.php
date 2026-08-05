<?php

namespace Modules\Education\Services;

use Illuminate\Support\Facades\DB;
use Modules\Education\Models\Halaqa;
use Modules\Education\Models\Student;

class StudentService
{

    public function list()
    {
        if (request()->has('has_halaqa')) {
            $hasHalaqaVal = request('has_halaqa');
            if (!in_array($hasHalaqaVal, ['0', '1', 0, 1], true)) {
                abort(422, __('messages.invalid_has_halaqa_value'));
            }
        }

        return Student::query()
            ->with([
                'mosque',
                'parent',
                'halaqats:id,name,teacher_id',
                'halaqats.teacher:id,name',
                'evaluations' => fn($q) => $q->latest('evaluated_at')
            ])
            ->withAvg('evaluations', 'score')
            ->forUser(auth()->user())

            ->when(request('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('first_name', 'ILIKE', "%{$search}%")
                        ->orWhere('last_name', 'ILIKE', "%{$search}%")
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) ILIKE ?", ["%{$search}%"]);
                });
            })

            ->when(request('status'), fn($q, $status) => $q->where('status', $status))

            ->when(request()->has('has_halaqa'), function ($q) {
                if (request('has_halaqa') == 1) {
                    $q->whereHas('halaqats');
                } else {
                    $q->whereDoesntHave('halaqats');
                }
            })

            // §8: فلتر الطلاب القابلين للإضافة لحلقة معينة (حيث الطالب ليس في هذه الحلقة)
            ->when(request('assignable_to_halaqa'), function ($q, $halaqaId) {
                $q->whereDoesntHave('halaqats', function ($sub) use ($halaqaId) {
                    $sub->where('halaqats.id', $halaqaId);
                });
            })

            ->latest()
            ->paginate(request('per_page', 10));
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
        return Student::with([
            'mosque',
            'parent',
            'halaqats:id,name,teacher_id',
            'halaqats.teacher:id,name' // §7: معلم الحلقة
        ])
            ->with([
                'evaluations' => fn($q) => $q->latest('evaluated_at')
            ])
            ->withCount([
                'attendances as total_absent' => fn($q) => $q->whereIn('status', ['absent', 'absent_with_excuse']),
                'attendances as total_present' => fn($q) => $q->where('status', 'present')
            ])
            ->withAvg('evaluations', 'score')
            ->forUser(auth()->user())
            ->findOrFail($id);
    }

    public function search(array $filters)
    {
        return Student::query()
            ->with(['mosque', 'parent', 'halaqats.teacher'])
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

    public function update(int $id, array $data): Student
    {
        $student = Student::query()
            ->forUser(auth()->user())
            ->findOrFail($id);

        $student->update($data);

        return $student->load(['mosque', 'parent', 'halaqats.teacher']);
    }

    public function delete($id)
    {
        Student::findOrFail($id)->delete();
    }



    public function approve(int $id, array $data = [])
    {
        $user = auth()->user();

        return DB::transaction(function () use ($id, $data, $user) {
            $student = Student::where('mosque_id', $user->mosque_id)->findOrFail($id);

            if ($student->status === 'active') {
                return ['error' => true, 'message' => __('messages.student_already_active')];
            }

            if ($student->status === 'rejected') {
                return ['error' => true, 'message' => __('messages.cannot_approve_rejected')];
            }

            if (!empty($data['halaqa_id'])) {
                $halaqa = Halaqa::where('mosque_id', $user->mosque_id)->findOrFail($data['halaqa_id']);

                if ($halaqa->students()->count() >= $halaqa->capacity) {
                    return ['error' => true, 'message' => __('messages.capacity_full', ['remaining' => 0])];
                }

                $student->update(['halaqa_id' => $halaqa->id]);
            }

            $student->update(['status' => 'active']);

            $loadedStudent = $student->load(['mosque', 'parent', 'halaqats.teacher']);

            // 🎯 إطلاق حدث الموافقة وإرسال كائن الطالب محمل بالبيانات
            event(new \Modules\Education\Events\StudentApproved($loadedStudent));

            return [
                'error' => false,
                'data'  => $loadedStudent
            ];
        });
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

        $loadedStudent = $student->load(['mosque', 'parent']);


        event(new \Modules\Education\Events\StudentRejected($loadedStudent));

        return ['error' => false, 'data' => $loadedStudent];
    }

    public function transferHalaqa(int $id, array $data)
    {
        $user = auth()->user();

        return DB::transaction(function () use ($id, $data, $user) {
            $student = Student::query()
                ->forUser($user)
                ->findOrFail($id);

            $currentHalaqaId = $student->halaqa_id;

            if ((int) $currentHalaqaId !== (int) $data['from_halaqa_id']) {
                return [
                    'error' => true,
                    'message' => __('messages.student_not_in_old_halaqa')
                ];
            }

            $toHalaqa = Halaqa::where('mosque_id', $user->mosque_id)
                ->findOrFail($data['to_halaqa_id']);

            if ($toHalaqa->students()->count() >= $toHalaqa->capacity) {
                return [
                    'error' => true,
                    'message' => __('messages.capacity_full', ['remaining' => 0])
                ];
            }

            $student->update([
                'halaqa_id' => $toHalaqa->id,
            ]);

            $loadedStudent = $student->load(['mosque', 'parent', 'halaqats.teacher']);

            return [
                'error' => false,
                'message' => __('messages.transfer_success', ['name' => $toHalaqa->name]),
                'data' => $loadedStudent,
            ];
        });
    }

}
