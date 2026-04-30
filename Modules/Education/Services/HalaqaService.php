<?php

namespace Modules\Education\Services;

use Illuminate\Validation\ValidationException;
use Modules\Education\Models\Halaqa;

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
