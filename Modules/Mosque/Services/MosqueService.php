<?php

namespace Modules\Mosque\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Mosque\Services\FacilityService;
use Modules\Mosque\Models\Mosque;
use Modules\Mosque\Repositories\MosqueRepositoryInterface;

class MosqueService
{
    public function __construct(
        private readonly MosqueRepositoryInterface $mosqueRepository,
        private readonly FacilityService $facilityService
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
        return DB::transaction(function () use ($data) {

            $facilityIds = $data['facility_ids'] ?? [];
            unset($data['facility_ids']);

            $data['average_rating'] = 0;
            $data['reviews_count'] = 0;

            if (isset($data['image']) && $data['image']) {
                $data['image'] = $this->uploadImage($data['image']);
            }

            $mosque = $this->mosqueRepository->create($data);

            if (!empty($facilityIds)) {
                $this->facilityService->syncMosqueFacilities($mosque, $facilityIds);
            }

            return $this->getMosqueById($mosque->id);
        });
    }

    public function updateMosque(Mosque $mosque, array $data): Mosque
    {
        return DB::transaction(function () use ($mosque, $data) {

            $facilityIds = $data['facility_ids'] ?? null;
            unset($data['facility_ids']);

            if (isset($data['image']) && $data['image']) {

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
        return $image->store('mosques', 'public');
    }

    private function deleteImage(string $path): void
    {
        Storage::disk('public')->delete($path);
    }
}
