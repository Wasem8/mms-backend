<?php

namespace Modules\Mosque\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Mosque\Models\MosqueNeed;

interface MosqueNeedRepositoryInterface
{
    public function paginate(int $perPage = 10): LengthAwarePaginator;

    public function find(int $id): ?MosqueNeed;

    public function create(array $data): MosqueNeed;

    public function update(MosqueNeed $need, array $data): MosqueNeed;

    public function delete(MosqueNeed $need): bool;
}