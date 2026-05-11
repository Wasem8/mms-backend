<?php

namespace Modules\Mosque\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Mosque\Models\MosqueNeed;

class MosqueNeedRepository implements MosqueNeedRepositoryInterface
{
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return MosqueNeed::latest()->paginate($perPage);
    }

    public function find(int $id): ?MosqueNeed
    {
        return MosqueNeed::find($id);
    }

    public function create(array $data): MosqueNeed
    {
        return MosqueNeed::create($data);
    }

    public function update(MosqueNeed $need, array $data): MosqueNeed
    {
        $need->update($data);
        return $need;
    }

    public function delete(MosqueNeed $need): bool
    {
        return $need->delete();
    }
}
