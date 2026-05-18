<?php

namespace Modules\Community\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Modules\Community\Models\Sermon;
use Modules\Community\Repositories\SermonRepositoryInterface;

class SermonService
{
    protected $sermonRepo;

    public function __construct(SermonRepositoryInterface $sermonRepo)
    {
        $this->sermonRepo = $sermonRepo;
    }

    public function getAllSermons()
    {
        return $this->sermonRepo->getAll();
    }

    public function getPendingSermons()
    {
        return $this->sermonRepo->getAllPending();
    }

    public function uploadSermon(array $data, $mosqueManagerId, array $attachments = [])
    {
        return DB::transaction(function () use ($data, $mosqueManagerId, $attachments) {

            $data['mosque_manager_id'] = $mosqueManagerId;
            $data['status'] = 'Pending';

            $sermon = $this->sermonRepo->create($data);

            $attachmentRecords = [];

            foreach ($attachments as $attachment) {
                if ($attachment instanceof UploadedFile) {
                    $filePath = $this->uploadImage($attachment);

                    $attachmentRecords[] = [
                        'file_path' => $filePath,
                        'file_type' => $attachment->getClientMimeType(),
                    ];
                }
            }

            if (!empty($attachmentRecords)) {
                $this->sermonRepo->attachAttachments($sermon, $attachmentRecords);
                $sermon->load('attachments');
            }

            return $sermon;
        });
        }

        public function getSermionById($sermonId)
        {
            $sermon = $this->sermonRepo->findById($sermonId);
            if ($sermon) {
                $sermon->load('attachments');
            }
            return $sermon;
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

    public function approveSermon($sermonId, $regionManagerId, $notes = null)
    {
        return $this->sermonRepo->updateStatus($sermonId, 'Scheduled', $notes, $regionManagerId);
    }

    public function rejectSermon($sermonId, $regionManagerId, $notes)
    {
        return $this->sermonRepo->updateStatus($sermonId, 'Rejected', $notes, $regionManagerId);
    }

    public function markPastSermonsAsCompleted()
    {

        $updatedCount = Sermon::where('status', 'scheduled')
            ->whereDate('sermon_date', '<', now()->toDateString())
            ->update(['status' => 'completed']);

        return $updatedCount;
    }
}
