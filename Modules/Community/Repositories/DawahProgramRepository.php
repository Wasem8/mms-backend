<?php
namespace Modules\Community\Repositories;

use Modules\Community\Models\DawahProgram;


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

public function checkConflict(int $mosqueId, int $spaceId, string $date, string $startTime, string $endTime): bool
{
return DawahProgram::where('mosque_id', $mosqueId)
->where('space_id', $spaceId)
->where('date', $date)
->where(function ($query) use ($startTime, $endTime) {
$query->whereBetween('start_time', [$startTime, $endTime])
->orWhereBetween('end_time', [$startTime, $endTime]);
})
->exists();
}

public function getProgramsByMosque(int $mosqueId)
{
return DawahProgram::where('mosque_id', $mosqueId)->with(['space'])->get();
}
}