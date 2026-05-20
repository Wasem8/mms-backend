<?php

namespace Modules\Education\Services;

use Illuminate\Validation\ValidationException;
use Modules\Common\Services\NotificationService;
use Modules\Education\Events\EvaluationUpdated;
use Modules\Education\Events\StudentEvaluated;
use Modules\Education\Models\Evaluation;
use Modules\Education\Models\Halaqa;

class EvaluationService
{
    public function store(array $data)
    {
        $user = auth()->user();

        $halaqa = Halaqa::with('students')->findOrFail($data['halaqa_id']);

        if ($user->isTeacher() && $halaqa->teacher_id !== $user->id) {
            throw ValidationException::withMessages([
                'halaqa' => [__('messages.unauthorized_evaluation')]
            ]);
        }

        if (! $halaqa->students->pluck('id')->contains($data['student_id'])) {
            throw ValidationException::withMessages([
                'student_id' => [__('messages.student_not_in_halaqa')]
            ]);
        }

        $evaluation = Evaluation::updateOrCreate(
            [
                'halaqa_id' => $data['halaqa_id'],
                'student_id' => $data['student_id'],
                'evaluated_at' => $data['evaluated_at'] ?? now()->toDateString(),
            ],
            [
                'score' => $data['score'],
                'notes' => $data['notes'] ?? null,
                'surah_name' => $data['surah_name'] ?? null,
                'from_ayah' => $data['from_ayah'] ?? null,
                'to_ayah' => $data['to_ayah'] ?? null,
            ]
        );

        event(new StudentEvaluated($evaluation));

        return $evaluation;
    }

    public function getMosqueEvaluations($mosqueId, $filters = [])
    {
        return Evaluation::with(['student', 'halaqa'])
            ->whereHas('halaqa', fn($q) => $q->where('mosque_id', $mosqueId))
            ->when(!empty($filters['halaqa_id']), fn($q) => $q->where('halaqa_id', $filters['halaqa_id']))
            ->when(!empty($filters['date']), fn($q) => $q->whereDate('evaluated_at', $filters['date']))
            ->latest('evaluated_at')
            ->paginate(15);
    }


    public function getTeacherEvaluations($teacherId, $filters = [])
    {
        return Evaluation::with(['student', 'halaqa'])
            ->whereHas('halaqa', fn($q) => $q->where('teacher_id', $teacherId))
            ->when(!empty($filters['date']), fn($q) => $q->whereDate('evaluated_at', $filters['date']))
            ->latest('evaluated_at')
            ->paginate(15);
    }

    public function getParentEvaluations($parentId, $filters = [])
    {
        return Evaluation::with(['student', 'halaqa'])
            ->whereHas('student', fn($q) => $q->where('parent_id', $parentId))
            ->when(!empty($filters['student_id']), fn($q) => $q->where('student_id', $filters['student_id']))
            ->latest()
            ->paginate(15);
    }

    public function getEvaluationById($id)
    {
        $user = auth()->user();
        $query = Evaluation::with(['student', 'halaqa']);

        if ($user->isParent()) {
            $query->whereHas('student', fn($q) => $q->where('parent_id', $user->id));
        } elseif ($user->isTeacher()) {
            $query->whereHas('halaqa', fn($q) => $q->where('teacher_id', $user->id));
        }


        return $query->findOrFail($id);
    }

    public function update($id, array $data)
    {
        $user = auth()->user();
        $evaluation = Evaluation::findOrFail($id);

        if ($user->isTeacher() && $evaluation->halaqa->teacher_id !== $user->id) {
            throw ValidationException::withMessages([
                'auth' => [__('messages.unauthorized_edit_evaluation')]
            ]);
        }

        if ($user->isSupervisor() && $evaluation->halaqa->mosque_id !== $user->mosque_id) {
            throw ValidationException::withMessages([
                'auth' => [__('messages.evaluation_not_belongs_to_mosque')]
            ]);
        }

        $evaluation->update([
            'score' => $data['score'] ?? $evaluation->score,
            'notes' => $data['notes'] ?? $evaluation->notes,
            'evaluated_at' => $data['evaluated_at'] ?? $evaluation->evaluated_at,
            'surah_name' => $data['surah_name'] ?? $evaluation->surah_name,
            'from_ayah' => $data['from_ayah'] ?? $evaluation->from_ayah,
            'to_ayah' => $data['to_ayah'] ?? $evaluation->to_ayah,
        ]);

        event(new EvaluationUpdated($evaluation));

        return $evaluation;
    }

    public function delete($id)
    {
        $user = auth()->user();
        $evaluation = Evaluation::findOrFail($id);

        if ($user->isTeacher() && $evaluation->halaqa->teacher_id !== $user->id) {
            throw new \Exception(__('messages.unauthorized_delete_evaluation'));
        }

        if ($user->isSupervisor() && $evaluation->halaqa->mosque_id !== $user->mosque_id) {
            throw new \Exception(__('messages.evaluation_not_belongs_to_mosque'));
        }

        return $evaluation->delete();
    }
}
