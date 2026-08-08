<?php

namespace Modules\Mosque\Repositories;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\Mosque\Models\MosqueTask;
use Modules\Mosque\DTOs\CreateMosqueTaskDTO;
use Modules\Mosque\DTOs\UpdateMosqueTaskDTO;

interface MosqueTaskRepositoryInterface
{
    public function findById(int $mosqueId, int $id): ?MosqueTask;

    public function findForDate(int $mosqueId, Carbon $date, ?string $status = null, ?string $category = null): Collection;

    public function countsByDate(int $mosqueId, array $dates): array;

    public function create(CreateMosqueTaskDTO $dto): MosqueTask;

    public function update(MosqueTask $task, UpdateMosqueTaskDTO $dto): MosqueTask;

    public function toggleComplete(MosqueTask $task): MosqueTask;

    public function delete(MosqueTask $task): bool;

    public function findForRange(int $mosqueId, Carbon $from, Carbon $to, ?string $status = null, ?string $category = null): Collection;

    public function countForRange(int $mosqueId, Carbon $from, Carbon $to): int;
}
