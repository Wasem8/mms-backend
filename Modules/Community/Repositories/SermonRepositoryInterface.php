<?php

namespace Modules\Community\Repositories;

use Modules\Community\Models\Sermon;
use Illuminate\Support\Collection;

interface SermonRepositoryInterface
{
    public function create(array $data): Sermon;
    public function findById(int $id): ?Sermon;
    public function updateStatus(Sermon $sermon, string $status, ?string $notes = null, ?int $regionManagerId = null): bool;
    public function delete(Sermon $sermon): bool;
    public function getExpiredPendingSermons(string $currentDate);
    public function search(array $filters, int $perPage = 15);
    public function mostSelected(int $limit = 10, ?string $fridayDateFrom = null, ?string $fridayDateTo = null): Collection;
}
