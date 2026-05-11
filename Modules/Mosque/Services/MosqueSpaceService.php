<?php

namespace Modules\Mosque\Services;

use Modules\Mosque\Models\Mosque_space;
use Modules\Mosque\Models\MosqueSpace;
use Modules\Mosque\Repositories\MosqueSpaceRepositoryInterface;

class MosqueSpaceService
{
    public function __construct(
        private readonly MosqueSpaceRepositoryInterface $spaceRepository
    ) {}


    public function createSpace(array $data): MosqueSpace
    {
        return $this->spaceRepository->create($data);
    }


    public function updateSpace(MosqueSpace $space, array $data): MosqueSpace
    {
        return $this->spaceRepository->update($space, $data);
    }


    public function deleteSpace(MosqueSpace $space): void
    {
        $this->spaceRepository->delete($space);
    }


    public function getSpacesByMosque(int $mosqueId)
    {
        return $this->spaceRepository->getByMosque($mosqueId);
    }


    public function calculateTotalMosqueCapacity(int $mosqueId): int
    {
        $spaces = $this->getSpacesByMosque($mosqueId);

        return $spaces->sum('capacity');
    }
}
