<?php

namespace Modules\Mosque\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Mosque\Models\MosqueSpace;

class MosqueSpaceRepository implements MosqueSpaceRepositoryInterface
{
    public function create(array $data): MosqueSpace
    {
        return MosqueSpace::create($data);
    }

    public function update(MosqueSpace $space, array $data): MosqueSpace
    {
        $space->update($data);

        return $space;
    }

    public function delete(MosqueSpace $space): void
    {
        $space->delete();
    }

    public function getByMosque(int $mosqueId): Collection
    {

        return MosqueSpace::where('mosque_id', $mosqueId)->get();
    }
}
