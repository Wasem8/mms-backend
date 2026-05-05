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

    public function list($filters = [])
    {
        $user = auth()->user();

        $query = Evaluation::with(['student', 'halaqa']);

        if ($user->isSupervisor()) {
            $query->whereHas('halaqa', function ($q) use ($user) {
                $q->where('mosque_id', $user->mosque_id);
            });
        }

        if ($user->isParent()) {
            $query->whereHas('student', function ($q) use ($user) {
                $q->where('parent_id', $user->id);
            });
        }

        return $query->latest()->paginate(15);
    }
}
