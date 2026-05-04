<?php

namespace Modules\Mosque\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Modules\Mosque\Services\FacilityService;
use Modules\Mosque\Models\Mosque;
use Modules\Mosque\Repositories\MosqueRepositoryInterface;

class MosqueService
{
    public function __construct(
        private readonly MosqueRepositoryInterface $mosqueRepository,
        private readonly FacilityService $facilityService,
    ) {}

    public function getAllMosques(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->mosqueRepository->getAllPaginated($filters, $perPage);
    }

    public function searchMosques(string $query, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->mosqueRepository->searchMosques($query, $filters, $perPage);
    }

    public function getMosqueById(int $id): ?Mosque
    {
        return $this->mosqueRepository->findById($id, [
            'facilities',
            'manager:id,name,email',
        ]);
    }

    public function createMosque(array $data): Mosque
    {
        $facilityIds = $data['facility_ids'] ?? [];
        unset($data['facility_ids']);

        $data['average_rating'] = 0;
        $data['reviews_count'] = 0;

        if (!empty($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image'] = $this->uploadImage($data['image']);
        }

        return DB::transaction(function () use ($data, $facilityIds) {

            $mosque = $this->mosqueRepository->create($data);

            if (!empty($facilityIds)) {
                $this->facilityService->syncMosqueFacilities($mosque, $facilityIds);
            }

            return $mosque;
        });
    }
    public function updateMosque(Mosque $mosque, array $data): Mosque
    {
        return DB::transaction(function () use ($mosque, $data) {

            $facilityIds = $data['facility_ids'] ?? null;
            unset($data['facility_ids']);

            if (!empty($data['image']) && $data['image'] instanceof UploadedFile) {

                if ($mosque->image) {
                    $this->deleteImage($mosque->image);
                }

                $data['image'] = $this->uploadImage($data['image']);
            }
            $this->mosqueRepository->update($mosque, $data);

            if ($facilityIds !== null) {
                $this->facilityService->syncMosqueFacilities($mosque, $facilityIds);
            }

            return $this->getMosqueById($mosque->id);
        });
    }
    public function deleteMosque(Mosque $mosque): void
    {
        DB::transaction(function () use ($mosque) {
            $mosque->facilities()->detach();

            $this->mosqueRepository->delete($mosque);
        });
    }

    public function toggleFeaturedStatus(Mosque $mosque): Mosque
    {
        $this->mosqueRepository->update($mosque, [
            'is_featured' => !$mosque->is_featured,
        ]);

        return $this->getMosqueById($mosque->id);
    }

    public function updateStatus(Mosque $mosque, string $status): Mosque
    {
        $this->mosqueRepository->update($mosque, [
            'status' => $status,
        ]);

        return $this->getMosqueById($mosque->id);
    }
    public function getMosquesByCity(string $city)
    {
        return $this->mosqueRepository->getByCity($city);
    }

    public function getFeaturedMosques(int $limit = 10)
    {
        return $this->mosqueRepository->getFeatured($limit);
    }

    public function updateMosqueStatus(Mosque $mosque, string $status): Mosque
    {
        $this->mosqueRepository->update($mosque, [
            'status' => $status,
        ]);

        return $this->getMosqueById($mosque->id);
    }

    public function updateMosqueRating(Mosque $mosque, float $averageRating, int $reviewsCount): bool
    {
        return $this->mosqueRepository->update($mosque, [
            'average_rating' => $averageRating,
            'reviews_count' => $reviewsCount,
        ]);
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
    private function deleteImage(string $url): void
    {
        $bucket = env('SUPABASE_BUCKET');

        $fileName = basename($url);

        $path = $bucket . '/' . $fileName;

        $deleteUrl = env('SUPABASE_URL') . '/storage/v1/object/' . $path;

        Http::withHeaders([
            'apikey' => env('SUPABASE_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_KEY'),
        ])->delete($deleteUrl);
    }
}
