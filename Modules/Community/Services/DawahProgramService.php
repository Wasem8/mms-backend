<?php

namespace Modules\Community\Services;

use Modules\Community\Repositories\DawahProgramRepositoryInterface;
use Modules\Community\Models\DawahProgram;
use Modules\Mosque\Models\Mosque;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
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

        abort_if(
            !$mosque,
            404,
            'Mosque not found.'
        );

        abort_if(
            $mosque->manager_id !== Auth::id(),
            403,
            'Unauthorized.'
        );

        if (
            isset($data['image']) &&
            $data['image'] instanceof UploadedFile
        ) {
            $data['image'] = $this->uploadImage($data['image']);
        }

        if (
            isset($data['presenter_image']) &&
            $data['presenter_image'] instanceof UploadedFile
        ) {
            $data['presenter_image'] = $this->uploadImage($data['presenter_image']);
        }

        $schedules = $data['schedules'] ?? [];

        foreach ($schedules as $schedule) {

            abort_if(
                strtotime($schedule['end_time']) <= strtotime($schedule['start_time']),
                422,
                'End time must be after start time.'
            );

            // التحقق من التعارض
            $hasConflict = $this->checkConflict(
                $data['mosque_id'],
                $data['space_id'],
                $schedule['date'],
                $schedule['start_time'],
                $schedule['end_time']
            );

            abort_if(
                $hasConflict,
                409,
                "Conflict detected on {$schedule['date']} between {$schedule['start_time']} and {$schedule['end_time']}."
            );
        }

        return DB::transaction(function () use ($data, $schedules) {

            $program = $this->dawahProgramRepository->create($data);

            if (!empty($schedules)) {
                $this->dawahProgramRepository
                    ->createSchedules($program, $schedules);
            }

            return $program->load('schedules');
        });
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

        // Handle program image
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            if ($program->image) {
                $this->deleteImage($program->image);
            }
            $data['image'] = $this->uploadImage($data['image']);
        } else {
            $data['image'] = $program->image;
        }

        // Handle presenter image
        if (isset($data['presenter_image']) && $data['presenter_image'] instanceof UploadedFile) {
            if ($program->presenter_image) {
                $this->deleteImage($program->presenter_image);
            }
            $data['presenter_image'] = $this->uploadImage($data['presenter_image']);
        } else {
            $data['presenter_image'] = $program->presenter_image;
        }

        // Validate schedule conflicts before updating
        $schedules = $data['schedules'] ?? [];
        $spaceId = $data['space_id'] ?? $program->space_id;
        $mosqueId = $data['mosque_id'] ?? $program->mosque_id;

        foreach ($schedules as $schedule) {
            if ($this->checkConflict(
                $mosqueId,
                $spaceId,
                $schedule['date'],
                $schedule['start_time'],
                $schedule['end_time'],
                $program->id  // Exclude current program's schedules from conflict check
            )) {
                throw new \Exception("Conflict detected on {$schedule['date']} between {$schedule['start_time']} and {$schedule['end_time']}");
            }
        }

        $program = $this->dawahProgramRepository->update($program, $data);

        // Sync schedules if provided
        if (!empty($schedules)) {
            $this->dawahProgramRepository->syncSchedules($program, $schedules);
        }

        return $program;
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

        if ($program->presenter_image) {
            $this->deleteImage($program->presenter_image);
        }

        return $this->dawahProgramRepository->delete($program);
    }

    /**
     * Check for space booking conflicts in program_schedules.
     * Pass $excludeProgramId when updating to ignore the program's own schedules.
     */
    public function checkConflict(
        int $mosqueId,
        int $spaceId,
        string $date,
        string $startTime,
        string $endTime,
        ?int $excludeProgramId = null
    ): bool {
        return $this->dawahProgramRepository->checkConflict(
            $mosqueId,
            $spaceId,
            $date,
            $startTime,
            $endTime,
            $excludeProgramId
        );
    }

    public function getProgramsByMosque(int $mosqueId)
    {
        return $this->dawahProgramRepository->getProgramsByMosque($mosqueId);
    }

    private function uploadImage(UploadedFile $image): string
    {
        $fileName = uniqid() . '.' . $image->getClientOriginalExtension();

        $baseUrl = config('services.supabase.url');
        $bucket  = config('services.supabase.bucket');
        $key     = config('services.supabase.key');

        if (!$baseUrl || !$bucket || !$key) {
            throw new \Exception('Supabase config missing');
        }

        $path      = $bucket . '/' . $fileName;
        $uploadUrl = rtrim($baseUrl, '/') . '/storage/v1/object/' . $path;

        $response = Http::withHeaders([
            'apikey'        => $key,
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
        $bucket  = config('services.supabase.bucket');
        $key     = config('services.supabase.key');

        if (!$baseUrl || !$bucket || !$key) return;

        Http::withHeaders([
            'apikey'        => $key,
            'Authorization' => 'Bearer ' . $key,
            'Content-Type'  => 'application/json',
        ])->delete("{$baseUrl}/storage/v1/object/{$bucket}/{$path}");
    }
}
