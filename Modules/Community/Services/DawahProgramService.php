<?php

namespace Modules\Community\Services;

use Modules\Community\Repositories\DawahProgramRepositoryInterface;
use Modules\Community\Models\DawahProgram;
use Modules\Mosque\Models\Mosque;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class DawahProgramService
{
    protected $dawahProgramRepository;

    public function __construct(DawahProgramRepositoryInterface $dawahProgramRepository)
    {
        $this->dawahProgramRepository = $dawahProgramRepository;
    }

    public function getAllPrograms(int $perPage = 10)
    {
        return $this->dawahProgramRepository->paginate($perPage);
    }

    public function getProgramById(int $id): ?DawahProgram
    {
        return $this->dawahProgramRepository->find($id);
    }

    public function createProgram(array $data): DawahProgram
    {
        $mosque = Mosque::find($data['mosque_id']);

        if (!$mosque) {
            throw new \Exception('Mosque not found');
        }

        if ($mosque->manager_id !== Auth::id()) {
            throw new \Exception('Unauthorized');
        }

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image'] = $this->uploadImage($data['image']);
        }

        if ($this->checkConflict(
            $data['mosque_id'],
            $data['space_id'],
            $data['date'],
            $data['start_time'],
            $data['end_time']
        )) {
            throw new \Exception('Conflict detected');
        }

        return $this->dawahProgramRepository->create($data);
    }

    public function updateProgram(DawahProgram $program, array $data): DawahProgram
    {
        $mosque = Mosque::find($program->mosque_id);

        if (!$mosque) {
            throw new \Exception('Mosque not found');
        }

        if ($mosque->manager_id !== Auth::id()) {
            throw new \Exception('Unauthorized');
        }

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {

            if ($program->image) {
                $this->deleteImage($program->image);
            }

            $data['image'] = $this->uploadImage($data['image']);
        } else {
            $data['image'] = $program->image;
        }

        if ($this->checkConflict(
            $data['mosque_id'] ?? $program->mosque_id,
            $data['space_id'] ?? $program->space_id,
            $data['date'] ?? $program->date,
            $data['start_time'] ?? $program->start_time,
            $data['end_time'] ?? $program->end_time
        )) {
            throw new \Exception('Conflict detected');
        }

        return $this->dawahProgramRepository->update($program, $data);
    }

    public function deleteProgram(Mosque $mosque, DawahProgram $program): bool
    {
        if ($program->mosque_id !== $mosque->id) {
            throw new \Exception('البرنامج غير موجود في هذا المسجد');
        }

        if ($mosque->manager_id !== Auth::id()) {
            throw new \Exception('Unauthorized');
        }

        if ($program->image) {
            $this->deleteImage($program->image);
        }

        return $this->dawahProgramRepository->delete($program);
    }

    public function checkConflict(int $mosqueId, int $spaceId, string $date, string $startTime, string $endTime): bool
    {
        return $this->dawahProgramRepository->checkConflict($mosqueId, $spaceId, $date, $startTime, $endTime);
    }

    public function getProgramsByMosque(int $mosqueId)
    {
        return $this->dawahProgramRepository->getProgramsByMosque($mosqueId);
    }


    private function uploadImage(UploadedFile $image): string
    {
        $fileName = uniqid() . '.' . $image->getClientOriginalExtension();

        $baseUrl = config('services.supabase.url');
        $bucket = config('services.supabase.bucket');
        $key = config('services.supabase.key');

        if (!$baseUrl || !$bucket || !$key) {
            throw new \Exception('Supabase config missing');
        }

        $path = $bucket . '/' . $fileName;

        $uploadUrl = rtrim($baseUrl, '/') . '/storage/v1/object/' . $path;

        $response = Http::withHeaders([
            'apikey' => $key,
            'Authorization' => 'Bearer ' . $key,
        ])->withBody(
            file_get_contents($image->getRealPath()),
            $image->getMimeType()
        )->put($uploadUrl);

        if (!$response->successful()) {
            throw new \Exception('Upload failed: ' . $response->body());
        }

        return rtrim($baseUrl, '/') . '/storage/v1/object/public/' . $path;
    }

    private function deleteImage(string $path): void
    {
        $baseUrl = config('services.supabase.url');
        $bucket = config('services.supabase.bucket');
        $key = config('services.supabase.key');

        if (!$baseUrl || !$bucket || !$key) return;

        Http::withHeaders([
            'apikey' => $key,
            'Authorization' => 'Bearer ' . $key,
            'Content-Type' => 'application/json',
        ])->delete(
            "{$baseUrl}/storage/v1/object/{$bucket}/{$path}"
        );
    }
}
