<?php

namespace Modules\Education\Services;

use Illuminate\Validation\ValidationException;
use Modules\Education\Models\Halaqa;
use Modules\Education\Models\Student;

class HalaqaService
{
    public function list()
    {
        $user = auth()->user();
        $query = Halaqa::with('teacher');


        if ($user->isSupervisor()) {
            $query->where('mosque_id', $user->mosque_id);
        }

        return $query->latest()->paginate(10);
    }

    public function create(array $data)
    {
        $user = auth()->user();

        if ($user->isSupervisor() && !$user->mosque_id) {
            throw new \Exception('هذا المشرف غير مرتبط بمسجد، لا يمكنه إنشاء حلقات.');
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
        }

        return $query->findOrFail($id);
    }

    public function update($id, array $data)
    {

        $halaqa = $this->find($id);
        $halaqa->update($data);

        return $halaqa;
    }

    public function delete($id)
    {
        $halaqa = Halaqa::findOrFail($id);
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
                'students' => ['المعرفات التالية غير موجودة في النظام: ' . implode(', ', $missingIds)]
            ]);
        }

        $errors = [];
        foreach ($foundStudents as $student) {
            if ($student->mosque_id !== $halaqa->mosque_id) {
                $errors[] = "الطالب ({$student->first_name}) يتبع لمسجد آخر.";
            }

            if ($student->status !== 'active') {
                $errors[] = "الطالب ({$student->first_name}) حالته حالياً ({$student->status}) ولا يمكن إضافته للحلقة.";
            }

            $isAlreadyInHalaqa = $halaqa->students()->where('student_id', $student->id)->exists();
            if ($isAlreadyInHalaqa) {
                $errors[] = "الطالب ({$student->first_name}) موجود بالفعل في هذه الحلقة.";
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
                'capacity' => ["عذراً، الحلقة لا تستوعب هذا العدد. المقاعد المتبقية: {$remaining}"]
            ]);
        }

        $halaqa->students()->syncWithoutDetaching(
            collect($studentIds)->mapWithKeys(fn ($id) => [
                $id => ['joined_at' => now(), 'status' => 'active']
            ])->toArray()
        );
    }

    public function detachStudent($halaqaId, $studentId)
    {
        $halaqa = Halaqa::findOrFail($halaqaId);

        $exists = $halaqa->students()->where('student_id', $studentId)->exists();

        if (!$exists) {
            throw ValidationException::withMessages([
                'student' => ['هذا الطالب غير مسجل في هذه الحلقة.']
            ]);
        }

        $halaqa->students()->detach($studentId);
    }
}
