<?php

namespace Modules\Mosque\Services;

use Modules\Mosque\Repositories\MosqueNeedRepositoryInterface;
use Modules\Mosque\Models\MosqueNeed;

class MosqueNeedsService
{
    public function __construct(
        private MosqueNeedRepositoryInterface $repository
    ) {}

    public function list(int $perPage = 10)
    {
        return $this->repository->paginate($perPage);
    }

    public function get(int $id): MosqueNeed
    {
        return $this->repository->find($id)
            ?? throw new \Exception('Need not found');
    }

    public function create(array $data): MosqueNeed
    {

        if (isset($data['image']) && $data['image']) {
            $data['image'] = $this->uploadImage($data['image']);
        }

        $data['status'] = $data['status'] ?? 'open';
        $data['collected_amount'] = $data['collected_amount'] ?? 0;

        return $this->repository->create($data);
    }

    public function update(int $id, array $data): MosqueNeed
    {
        $need = $this->get($id);

        return $this->repository->update($need, $data);
    }

    public function delete(int $id): bool
    {
        $need = $this->get($id);

        return $this->repository->delete($need);
    }



    private function uploadImage($image): string
    {
        return $image->store('needs', 'public');
    }
}
