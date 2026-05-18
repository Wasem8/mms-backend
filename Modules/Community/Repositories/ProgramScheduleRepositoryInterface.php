<?php

namespace Modules\Community\Repositories;

use Modules\Community\Models\ProgramSchedule;

interface ProgramScheduleRepositoryInterface {


    public function getByProgram(int $programId, array $filters = []);
    public function findById(int $id);
    public function findByProgramAndId(int $programId, int $scheduleId);
    public function create(int $programId, array $data);
    public function update(ProgramSchedule $schedule, array $data): ProgramSchedule;
    public function delete(ProgramSchedule $schedule): bool;

}
