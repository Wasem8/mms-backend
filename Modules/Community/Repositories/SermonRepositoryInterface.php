<?php

namespace Modules\Community\Repositories;

use Modules\Community\Models\Sermon;

interface SermonRepositoryInterface
{
    public function create(array $data): Sermon;
    public function findById(int $id): ?Sermon;
    public function updateStatus(Sermon $sermon, string $status): bool;
    public function delete(Sermon $sermon): bool;
    public function getExpiredPendingSermons(string $currentDate);
    public function search(array $filters, int $perPage = 15);
}
