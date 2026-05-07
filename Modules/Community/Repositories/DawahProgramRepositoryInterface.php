<?php

namespace Modules\Community\Repositories;

use Modules\Community\Models\DawahProgram;

interface DawahProgramRepositoryInterface
{
    public function paginate(int $perPage = 10);

    public function find(int $id): ?DawahProgram;

    public function create(array $data): DawahProgram;

    public function update(DawahProgram $program, array $data): DawahProgram;

    public function delete(DawahProgram $program): bool;

    public function checkConflict(int $mosqueId, int $spaceId, string $date, string $startTime, string $endTime): bool;

    public function getProgramsByMosque(int $mosqueId);
}
