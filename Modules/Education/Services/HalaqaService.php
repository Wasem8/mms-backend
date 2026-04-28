<?php

namespace Modules\Education\Services;

use Illuminate\Validation\ValidationException;
use Modules\Education\Models\Halaqa;

class HalaqaService
{
    public function list()
    {
        return Halaqa::with('teacher')->latest()->paginate(10);
    }

    public function create(array $data)
    {
        return Halaqa::create($data);
    }

    public function find($id)
    {
        return Halaqa::with('students', 'teacher')->findOrFail($id);
    }

    public function update($id, array $data)
    {
        $halaqa = Halaqa::findOrFail($id);
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

        if ($halaqa->students()->count() + count($studentIds) > $halaqa->capacity) {
            throw ValidationException::withMessages([
                'students' => ['لا يمكن إضافة الطلاب، تم تجاوز الطاقة الاستيعابية للحلقة.']
            ]);
        }

        $halaqa->students()->syncWithoutDetaching(
            collect($studentIds)->mapWithKeys(fn ($id) => [
                $id => ['joined_at' => now(), 'status' => 'active']
            ])
        );
    }

    public function detachStudent($halaqaId, $studentId)
    {
        $halaqa = Halaqa::findOrFail($halaqaId);

        $halaqa->students()->detach($studentId);
    }
}
