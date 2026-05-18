<?php
namespace Modules\Community\Repositories;

use Modules\Community\Models\DawahProgram;
use Modules\Community\Models\ProgramSchedule;
use Illuminate\Support\Facades\DB;


class DawahProgramRepository implements DawahProgramRepositoryInterface
{
public function paginate(int $perPage = 10)
{
return DawahProgram::with(['mosque', 'space'])->paginate($perPage);
}

public function find(int $id): ?DawahProgram
{
return DawahProgram::with(['mosque', 'space'])->find($id);
}

public function create(array $data): DawahProgram
{
return DawahProgram::create($data);
}

public function update(DawahProgram $program, array $data): DawahProgram
{
$program->update($data);
return $program;
}

public function delete(DawahProgram $program): bool
{
return $program->delete();
}
    public function checkConflict(
        int $mosqueId,
        int $spaceId,
        string $date,
        string $startTime,
        string $endTime,
        ?int $excludeProgramId = null
    ): bool {
        return DB::table('program_schedules')
            ->join('dawah_programs', 'dawah_programs.id', '=', 'program_schedules.dawah_program_id')
            ->where('dawah_programs.mosque_id', $mosqueId)
            ->where('dawah_programs.space_id', $spaceId)
            ->where('program_schedules.date', $date)
            ->where('program_schedules.start_time', '<', $endTime)
            ->where('program_schedules.end_time', '>', $startTime)
            ->when($excludeProgramId, fn($q) => $q->where('dawah_programs.id', '!=', $excludeProgramId))
            ->exists();
    }


    public function getProgramsByMosque(int $mosqueId)
{
return DawahProgram::where('mosque_id', $mosqueId)->with(['space'])->get();
}
    public function createSchedules(DawahProgram $program, array $schedules): void
    {
        $program->schedules()->createMany($schedules);
    }

    public function syncSchedules(DawahProgram $program, array $schedules): void
    {
        $program->schedules()->delete();
        $program->schedules()->createMany($schedules);
    }
}
