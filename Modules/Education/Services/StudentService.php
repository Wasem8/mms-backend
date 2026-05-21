<?php

namespace Modules\Education\Services;

use Illuminate\Support\Facades\DB;
use Modules\Education\Models\Halaqa;
use Modules\Education\Models\Student;

class StudentService
{

    public function list()
    {
        return Student::query()
            ->with([
                'mosque',
                'parent',
                'halaqats:id,name'
            ])

            ->forUser(auth()->user())

            ->when(
                request('status'),
                fn($q, $status) => $q->where('status', $status)
            )

            ->when(request()->has('has_halaqa'), function ($q) {

                if (request('has_halaqa') == 1) {
                    $q->whereHas('halaqats');
                }

                if (request('has_halaqa') == 0) {
                    $q->whereDoesntHave('halaqats');
                }
            })

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

    public function update(int $id, array $data): Student
    {
        $student = Student::query()
            ->forUser(auth()->user())
            ->findOrFail($id);

        $student->update($data);

        return $student->load(['mosque', 'parent', 'halaqats']);
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
                $student->halaqats()->syncWithoutDetaching([$halaqa->id]);
            }

            $student->update(['status' => 'active']);

            $loadedStudent = $student->load(['mosque', 'parent', 'halaqats']);

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

}
