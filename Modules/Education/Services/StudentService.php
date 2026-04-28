<?php

namespace Modules\Education\Services;

use Modules\Education\Models\Student;

class StudentService
{
    public function list()
    {
        return Student::latest()->get();
    }

    public function create(array $data)
    {
        return Student::create($data);
    }

    public function find($id)
    {
        return Student::with('halaqat')->findOrFail($id);
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
