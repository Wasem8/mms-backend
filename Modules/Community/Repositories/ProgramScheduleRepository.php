<?php

namespace Modules\Community\Repositories;

use Modules\Community\Models\ProgramSchedule;
use Illuminate\Pagination\LengthAwarePaginator;

class ProgramScheduleRepository implements ProgramScheduleRepositoryInterface
{
    public function __construct(protected ProgramSchedule $model) {}

    public function getByProgram(int $programId, array $filters = []): LengthAwarePaginator
    {
        return $this->model
            ->where('dawah_program_id', $programId)
            ->when(isset($filters['date']), fn($q) => $q->whereDate('date', $filters['date']))
            ->when(isset($filters['from_date']), fn($q) => $q->whereDate('date', '>=', $filters['from_date']))
            ->when(isset($filters['to_date']), fn($q) => $q->whereDate('date', '<=', $filters['to_date']))
            ->orderBy('date')
            ->orderBy('start_time')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function findById(int $id): ?ProgramSchedule
    {
        return $this->model->with('dawahProgram')->find($id);
    }

    public function findByProgramAndId(int $programId, int $scheduleId): ?ProgramSchedule
    {
        return $this->model
            ->where('dawah_program_id', $programId)
            ->find($scheduleId);
    }

    public function create(int $programId, array $data): ProgramSchedule
    {
        return $this->model->create(array_merge($data, ['dawah_program_id' => $programId]));
    }

    public function update(ProgramSchedule $schedule, array $data): ProgramSchedule
    {
        $schedule->update($data);
        return $schedule->fresh();
    }

    public function delete(ProgramSchedule $schedule): bool
    {
        return $schedule->delete();
    }
    
    public function hasConflict(
        int $programId,
        string $date,
        string $startTime,
        string $endTime,
        ?int $ignoreScheduleId = null
    ): bool {

        $query = ProgramSchedule::query()
            ->where('dawah_program_id', $programId)
            ->where('date', $date)

            // التحقق من التداخل الزمني
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            });

        if ($ignoreScheduleId) {
            $query->where('id', '!=', $ignoreScheduleId);
        }

        return $query->exists();
    }
}
