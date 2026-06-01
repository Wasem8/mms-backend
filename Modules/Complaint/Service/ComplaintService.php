<?php

namespace Modules\Complaint\Service;

use Illuminate\Support\Facades\Log;
use Modules\Complaint\Repositories\ComplaintRepositoryInterface;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Modules\Complaint\Models\Complaint;
use Modules\Mosque\Models\Mosque;

class ComplaintService
{
    public function __construct(
        protected ComplaintRepositoryInterface $repository
    ) {}


    public function submitComplaint(array $data, array $files = [])
    {
        $data['complaint_number'] = 'CMP-' . date('Y') . '-' . strtoupper(Str::random(6));
        $data['status'] = 'pending';

        $complaint = $this->repository->create($data);

        if (!empty($files)) {
            $fileRecords = [];

            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {

                    $publicUrl = $this->uploadImage($file);

                    $fileRecords[] = [
                        'file' => $publicUrl,
                        'file_type' => $file->getClientMimeType(),
                    ];
                }
            }
            if (!empty($fileRecords)) {
                $this->repository->attachFiles($complaint, $fileRecords);
            }
        }
        return $complaint;
    }


    public function trackComplaint(string $complaintNumber)
    {
        return $this->repository->findByComplaintNumber($complaintNumber);
    }


    public function getComplaintsForAdmin(array $filters = [])
    {
        return $this->repository->getFiltered($filters);
    }


    public function updateStatus(int $complaintId, string $newStatus, int $adminId, ?string $note = null)
    {
        $complaint = $this->repository->find($complaintId);
        $oldStatus = $complaint->status;

        $this->repository->update($complaintId, [
            'status' => $newStatus,
            'admin_notes' => $note
        ]);

        $this->repository->logStatusChange($complaint, [
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'note' => $note,
            'changed_at' => now(),
            'changed_by' => $adminId,
        ]);


        return $this->repository->find($complaintId);
    }

    public function getComplaintDetails(int $id, array $filters = [])
    {
        $complaint = $this->repository->find($id);

        if (isset($filters['mosque_id']) && $complaint->mosque_id !== $filters['mosque_id']) {
            abort(403, 'غير مصرح لك بالوصول إلى هذه الشكوى.');
        }

        return $complaint;
    }

    public function getComplaintStatistics(array $filters = [])
    {
        $query = Complaint::query();

        if (isset($filters['mosque_id'])) {
            $query->where('mosque_id', $filters['mosque_id']);
        }
        return [
            'total_complaints' => $query->count(),
            'by_status' => [
                'pending' => (clone $query)->where('status', 'pending')->count(),
                'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
                'resolved' => (clone $query)->where('status', 'resolved')->count(),
                'canceled' => (clone $query)->where('status', 'canceled')->count(),
            ],
            'by_type' => $query->selectRaw('complaint_type, count(*) as count')
                ->groupBy('complaint_type')
                ->pluck('count', 'complaint_type'),
        ];
    }

    private function uploadImage(UploadedFile $image): string
    {
        try {
            // 1. فحص أمان مسبق: التأكد من أن الملف صالح وموجود في المسار المؤقت
            if (! $image->isValid() || ! $image->getRealPath()) {
                throw new \Exception('الملف غير صالح أو تم منعه من قِبل نظام ملفات Vercel. مسار الملف المفقود: ' . $image->getErrorMessage());
            }

            $fileName = uniqid() . '.' . $image->getClientOriginalExtension();
            $baseUrl  = config('services.supabase.url');
            $bucket   = config('services.supabase.bucket');
            $key      = config('services.supabase.key');

            $path = $bucket . '/' . $fileName;
            $uploadUrl = $baseUrl . '/storage/v1/object/' . $path;

            // 2. قراءة الملف بشكل محمي لتجنب الانهيار المفاجئ
            $fileContent = @file_get_contents($image->getRealPath());
            if ($fileContent === false) {
                throw new \Exception('تم منع قراءة محتوى الملف من المسار: ' . $image->getRealPath());
            }

            $response = Http::withHeaders([
                'apikey' => $key,
                'Authorization' => 'Bearer ' . $key,
                'Content-Type' => $image->getMimeType(),
            ])
                ->withBody($fileContent, $image->getMimeType())
                ->post($uploadUrl);

            // 3. التقاط أخطاء Supabase بوضوح
            if (! $response->successful()) {
                throw new \Exception('رفض من Supabase: ' . $response->body());
            }

            return $baseUrl . '/storage/v1/object/public/' . $path;
        } catch (\Throwable $e) {
            // 4. إجبار لارافل على إرجاع الخطأ الحقيقي كاستجابة بدلاً من شاشة 500 البيضاء
            abort(500, 'فشل الرفع: ' . $e->getMessage());
        }
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
