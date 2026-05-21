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

    public function getAllPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = MosqueNeed::query()->with('mosque');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['urgent'])) {
            $isUrgent = filter_var($filters['urgent'], FILTER_VALIDATE_BOOLEAN);
            $query->where('is_urgent', $isUrgent);
        }

        return $query->latest()->paginate($perPage);
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
