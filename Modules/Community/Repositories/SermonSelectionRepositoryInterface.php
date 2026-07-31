<?php

namespace Modules\Community\Repositories;

use Modules\Community\Models\SermonSelection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface SermonSelectionRepositoryInterface
{
    public function upsert(int $mosqueManagerId, int $sermonId, string $fridayDate): SermonSelection;
    public function findById(int $id): ?SermonSelection;
    public function search(array $filters, int $perPage = 15): LengthAwarePaginator;
    public function upcomingPerMosque(): Collection;
    public function delete(SermonSelection $selection): void;
}
