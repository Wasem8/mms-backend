<?php

namespace Modules\Education\Services;

use Modules\Education\Models\Student;

class StudentService
{
    public function list()
    {
        $user = auth()->user();
        $query = Student::query()->with(['mosque', 'parent']);

        if ($user->isSupervisor()) {
            $query->where('mosque_id', $user->mosque_id);
        } elseif ($user->isParent()) {
            $query->where('parent_id', $user->id);
        }


        return $query->latest()->paginate(10);
    }

    public function create(array $data)
    {
        $data['parent_id'] = auth()->id();
        $data['status'] = 'inactive';

        $student = Student::create($data);

        // تحميل علاقة المسجد ليتعرف عليها الـ Resource
        return $student->load(['mosque', 'parent']);
    }

    public function find($id)
    {
        $user = auth()->user();

        $query = Student::with(['mosque', 'parent', 'halaqat']);

        if ($user->isSupervisor()) {
            $query->where('mosque_id', $user->mosque_id);
        } elseif ($user->isParent()) {
            $query->where('parent_id', $user->id);
        }

        return $query->findOrFail($id);
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
}
