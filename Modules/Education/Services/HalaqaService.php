<?php

namespace Modules\Education\Services;

use Illuminate\Validation\ValidationException;
use Modules\Education\Models\Halaqa;
use Modules\Education\Models\Student;
use Modules\User\Models\User;

class HalaqaService
{
    public function list()
    {
        $user = auth()->user();
        $query = Halaqa::with('teacher');

        if ($user->isSupervisor()) {
            $query->where('mosque_id', $user->mosque_id);
        } elseif ($user->hasRole('teacher') || $user->isTeacher()) {
            $query->where('teacher_id', $user->id);
        }

        return $query->latest()->paginate(10);
    }
    public function create(array $data)
    {
        $user = auth()->user();

        if ($user->isSupervisor() && !$user->mosque_id) {
            throw new \Exception(__('messages.supervisor_no_mosque'));
        }

        if (!empty($data['teacher_id'])) {
            $this->ensureTeacherIsAvailable((int) $data['teacher_id']);
        }

        $data['mosque_id'] = $user->mosque_id;
        return Halaqa::create($data);
    }

    public function find($id, array $relations = [])
    {
        $user = auth()->user();
        $query = Halaqa::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        if ($user->isSupervisor()) {
            $query->where('mosque_id', $user->mosque_id);
        } elseif ($user->hasRole('teacher') || $user->isTeacher()) {
            $query->where('teacher_id', $user->id);
        }

        return $query->findOrFail($id);
    }

    public function update($id, array $data)
    {
        $halaqa = $this->find($id);

        // إذا جرى تعديل السعة، نتأكد من أنها لا تقل عن عدد الطلاب المقيدين حالياً
        if (isset($data['capacity'])) {
            $currentStudentsCount = $halaqa->students()->count();
            if ($data['capacity'] < $currentStudentsCount) {
                throw ValidationException::withMessages([
                    'capacity' => [__('messages.capacity_less_than_students', ['count' => $currentStudentsCount])]
                ]);
            }
        }

        if (!empty($data['teacher_id'])) {
            $this->ensureTeacherIsAvailable((int) $data['teacher_id'], $halaqa->id);
        }

        $halaqa->update($data);

        return $halaqa->fresh(['teacher']);
    }

    public function delete($id)
    {
        $halaqa = $this->find($id);
        $halaqa->delete();
    }

    /**
     * @throws \Exception
     */
    public function attachStudents($halaqaId, array $studentIds)
    {
        $halaqa = Halaqa::findOrFail($halaqaId);

        $foundStudents = Student::whereIn('id', $studentIds)->get();
        $foundIds = $foundStudents->pluck('id')->toArray();

        $missingIds = array_diff($studentIds, $foundIds);
        if (!empty($missingIds)) {
            throw ValidationException::withMessages([
                'students' => [__('messages.student_not_found', ['ids' => implode(', ', $missingIds)])]
            ]);
        }

        $errors = [];
        foreach ($foundStudents as $student) {
            if ($student->mosque_id !== $halaqa->mosque_id) {
                $errors[] = __('messages.student_another_mosque', ['name' => $student->first_name]);
            }

            if ($student->status !== 'active') {
                $errors[] = __('messages.student_not_active', ['name' => $student->first_name, 'status' => $student->status]);
            }

            if ($student->halaqa_id && $student->halaqa_id !== $halaqa->id) {
                $errors[] = __('messages.student_already_exists', ['name' => $student->first_name]);
            }

            if ($student->halaqa_id === $halaqa->id) {
                $errors[] = __('messages.student_already_exists', ['name' => $student->first_name]);
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages([
                'students' => $errors
            ]);
        }

        $currentCount = $halaqa->students()->count();
        if ($currentCount + count($studentIds) > $halaqa->capacity) {
            $remaining = $halaqa->capacity - $currentCount;
            throw ValidationException::withMessages([
                'capacity' => [__('messages.capacity_full', ['remaining' => $remaining])]
            ]);
        }

        Student::whereIn('id', $studentIds)->update([
            'halaqa_id' => $halaqa->id,
            'updated_at' => now(),
        ]);
    }

    public function detachStudent($halaqaId, $studentId)
    {
        $halaqa = Halaqa::findOrFail($halaqaId);

        $exists = $halaqa->students()->where('id', $studentId)->exists();

        if (!$exists) {
            throw ValidationException::withMessages([
                'student' => [__('messages.student_not_in_halaqa')]
            ]);
        }

        Student::where('id', $studentId)->update([
            'halaqa_id' => null,
            'updated_at' => now(),
        ]);
    }

    private function ensureTeacherIsAvailable(int $teacherId, ?int $exceptHalaqaId = null): void
    {
        $teacher = User::find($teacherId);

        if (!$teacher || (!$teacher->hasRole('teacher') && !$teacher->isTeacher())) {
            throw ValidationException::withMessages([
                'teacher_id' => [__('messages.user_not_teacher')]
            ]);
        }

        if ($teacher->status !== 'active') {
            throw ValidationException::withMessages([
                'teacher_id' => [__('messages.teacher_not_active')]
            ]);
        }

        $user = auth()->user();
        if ($user?->mosque_id && $teacher->mosque_id !== $user->mosque_id) {
            throw ValidationException::withMessages([
                'teacher_id' => [__('messages.teacher_another_mosque')]
            ]);
        }

        $query = Halaqa::where('teacher_id', $teacherId);

        if ($exceptHalaqaId) {
            $query->where('id', '!=', $exceptHalaqaId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'teacher_id' => [__('messages.teacher_already_has_halaqa')]
            ]);
        }
    }


}
