<?php

namespace Modules\Community\Services;

use Modules\Community\Models\ProgramSchedule;
use Modules\Community\Repositories\ProgramScheduleRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class ProgramScheduleService
{
    public function __construct(protected ProgramScheduleRepository $repository) {}

    public function getSchedulesByProgram(int $programId, array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getByProgram($programId, $filters);
    }

    public function getScheduleById(int $programId, int $scheduleId): ProgramSchedule
    {
        $schedule = $this->repository->findByProgramAndId($programId, $scheduleId);

        abort_if(!$schedule, 404, 'Schedule not found.');

        return $schedule;
    }

    public function createSchedule(int $programId, array $data): ProgramSchedule
    {
        $this->validateTimeRange($data['start_time'], $data['end_time']);
        $this->validateScheduleConflict(
            $programId,
            $data['date'],
            $data['start_time'],
            $data['end_time']
        );
        return $this->repository->create($programId, $data);
    }

    public function updateSchedule(int $programId, int $scheduleId, array $data): ProgramSchedule
    {
        $schedule = $this->getScheduleById($programId, $scheduleId);

        $date = $data['date'] ?? $schedule->date;

        $startTime = $data['start_time']
            ?? $schedule->start_time;

        $endTime = $data['end_time']
            ?? $schedule->end_time;

        $this->validateTimeRange(
            $startTime,
            $endTime
        );

        $this->validateScheduleConflict(
            $programId,
            $date,
            $startTime,
            $endTime,
            $schedule->id
        );

        return $this->repository->update($schedule, $data);
    }

    public function deleteSchedule(int $programId, int $scheduleId): void
    {
        $schedule = $this->getScheduleById($programId, $scheduleId);

        $this->repository->delete($schedule);
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    private function validateTimeRange(string $startTime, string $endTime): void
    {
        abort_if(
            strtotime($endTime) <= strtotime($startTime),
            422,
            'End time must be after start time.'
        );
    }

    private function validateScheduleConflict(
        int $programId,
        string $date,
        string $startTime,
        string $endTime,
        ?int $ignoreScheduleId = null
    ): void {

        $hasConflict = $this->repository->hasConflict(
            $programId,
            $date,
            $startTime,
            $endTime,
            $ignoreScheduleId
        );

        abort_if(
            $hasConflict,
            409,
            'يوجد تعارض مع جلسة أخرى بنفس الوقت.'
        );
    }
}
