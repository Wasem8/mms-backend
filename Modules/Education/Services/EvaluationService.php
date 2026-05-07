<?php

namespace Modules\Education\Services;

use Modules\Education\Models\Evaluation;
use Modules\Education\Models\Halaqa;
use Illuminate\Validation\ValidationException;

class EvaluationService
{
    public function store(array $data)
    {
        $user = auth()->user();

        $halaqa = Halaqa::with('students')->findOrFail($data['halaqa_id']);

        if ($user->isTeacher() && $halaqa->teacher_id !== $user->id) {
            throw ValidationException::withMessages([
                'halaqa' => ['غير مصرح لك بالتقييم لهذه الحلقة']
            ]);
        }

        if (! $halaqa->students->pluck('id')->contains($data['student_id'])) {
            throw ValidationException::withMessages([
                'student_id' => ['الطالب غير تابع لهذه الحلقة']
            ]);
        }

        return Evaluation::updateOrCreate(
            [
                'halaqa_id' => $data['halaqa_id'],
                'student_id' => $data['student_id'],
                'evaluated_at' => $data['evaluated_at'] ?? now()->toDateString(),
            ],
            [
                'score' => $data['score'],
                'notes' => $data['notes'] ?? null,
            ]
        );
    }

    public function getMosqueEvaluations($mosqueId, $filters = [])
    {
        return Evaluation::with(['student', 'halaqa'])
            ->whereHas('halaqa', fn($q) => $q->where('mosque_id', $mosqueId))
            ->when(!empty($filters['halaqa_id']), fn($q) => $q->where('halaqa_id', $filters['halaqa_id']))
            ->when(!empty($filters['date']), fn($q) => $q->whereDate('evaluation_date', $filters['date']))
            ->latest()
            ->paginate(15);
    }

    // للمعلم: جلب تقييمات الحلقات التي يدرسها فقط
    public function getTeacherEvaluations($teacherId, $filters = [])
    {
        return Evaluation::with(['student', 'halaqa'])
            ->whereHas('halaqa', fn($q) => $q->where('teacher_id', $teacherId))
            ->when(!empty($filters['date']), fn($q) => $q->whereDate('evaluation_date', $filters['date']))
            ->latest()
            ->paginate(15);
    }

    // لولي الأمر: جلب تقييمات أبنائه فقط
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
}
