<?php

namespace Modules\Mosque\Services;

use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Modules\Mosque\Repositories\MosqueNeedRepositoryInterface;
use Modules\Mosque\Models\MosqueNeed;

class MosqueNeedsService
{
    public function __construct(
        private MosqueNeedRepositoryInterface $repository
    ) {}

    public function listAggregate(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->repository->getAllPaginated($filters, $perPage);
    }

    public function list(int $perPage = 10)
    {
        return $this->repository->paginate($perPage);
    }

    public function get(int $id): MosqueNeed
    {
        return $this->repository->find($id)
            ?? throw new \Exception('Need not found');
    }

    public function getNeedForMosque($mosqueId, $needId)
    {
        $need = MosqueNeed::where('id', $needId)
            ->where('mosque_id', $mosqueId)
            ->first();

        if (! $need) {
            throw new Exception('Need not found or does not belong to the specified mosque.');
        }

        return $need;
    }

    public function getNeedById($needId){
        $need = MosqueNeed::where('id',$needId)
        ->first();

        if(! $need) {
            throw new Exception('Need not found');
        }
        return $need;

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
        $fileName = uniqid() . '.' . $image->getClientOriginalExtension();

        $baseUrl = config('services.supabase.url');
        $bucket = config('services.supabase.bucket');
        $key = config('services.supabase.key');

        $path = $bucket . '/' . $fileName;

        $uploadUrl = $baseUrl . '/storage/v1/object/' . $path;

        $response = Http::withHeaders([
            'apikey' => $key,
            'Authorization' => 'Bearer ' . $key,
        ])->attach(
            'file',
            file_get_contents($image),
            $fileName
        )->post($uploadUrl);

        if (!$response->successful()) {
            throw new \Exception('Upload failed: ' . $response->body());
        }

        return $baseUrl . '/storage/v1/object/public/' . $path;
    }
}
