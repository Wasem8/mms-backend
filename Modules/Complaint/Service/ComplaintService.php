<?php

namespace Modules\Complaint\Service;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Complaint\Models\Complaint;
use Modules\Complaint\Repositories\ComplaintRepositoryInterface;

class ComplaintService
{
    public function __construct(
        protected ComplaintRepositoryInterface $repository
    ) {}

    public function submitComplaint(array $data, array $files = [])
    {
        $data['complaint_number'] = 'CMP-'.date('Y').'-'.strtoupper(Str::random(6));
        $data['status'] = 'pending';

        $complaint = $this->repository->create($data);

        if (! empty($files)) {
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
            if (! empty($fileRecords)) {
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
            'admin_notes' => $note,
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

    public function getComplaintPageStats(int $mosqueId): array
    {
        $now = now();
        $query = fn () => Complaint::where('mosque_id', $mosqueId);

        // إجمالي الشكاوى
        $total = $query()->count();

        // شكاوى مفتوحة (pending + in_progress)
        $open = $query()
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();

        // شكاوى عاجلة (priority = high فقط حسب الـ migration)
        $urgent = $query()
            ->where('priority', 'high')
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();

        // تم الحل هذا الشهر
        $resolvedThisMonth = $query()
            ->where('status', 'resolved')
            ->whereYear('updated_at', $now->year)
            ->whereMonth('updated_at', $now->month)
            ->count();

        // متوسط الاستجابة بالساعات
        // من created_at للشكوى إلى changed_at لأول log بعد pending
        $avgResponseHours = Complaint::where('mosque_id', $mosqueId)
            ->where('status', '!=', 'pending')
            ->join('complaint_status_logs as csl', function ($join) {
                $join->on('csl.complaint_id', '=', 'complaints.id')
                    ->where('csl.new_status', '!=', 'pending')
                    ->whereRaw("csl.id = (
                SELECT MIN(id)
                FROM complaint_status_logs
                WHERE complaint_id = complaints.id
                    AND new_status != 'pending'
             )");
            })
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (csl.changed_at - complaints.created_at)) / 3600) as avg_hours')
            ->value('avg_hours');

        return [
            'total_complaints' => $total,
            'open_complaints' => $open,
            'urgent_complaints' => $urgent,
            'resolved_this_month' => $resolvedThisMonth,
            'avg_response_hours' => (int) round($avgResponseHours ?? 0),
        ];
    }

    public function getRecentComplaints(int $mosqueId, int $limit = 5): array
    {
        return Complaint::where('mosque_id', $mosqueId)
            ->with(['files'])
            ->latest()
            ->limit($limit)
            ->get()
            ->toArray();
    }

    private function uploadImage($image): string
    {
        $fileName = uniqid().'.'.$image->getClientOriginalExtension();

        $baseUrl = config('services.supabase.url');
        $bucket = config('services.supabase.bucket');
        $key = config('services.supabase.key');

        $path = $bucket.'/'.$fileName;

        $uploadUrl = $baseUrl.'/storage/v1/object/'.$path;
        $response = Http::withHeaders([
            'apikey' => $key,
            'Authorization' => 'Bearer '.$key,
        ])->attach(
            'file',
            file_get_contents($image),
            $fileName
        )->post($uploadUrl);

        if (! $response->successful()) {
            throw new \Exception('Upload failed: '.$response->body());
        }

        return $baseUrl.'/storage/v1/object/public/'.$path;
    }

    private function deleteImage(string $url): void
    {
        $bucket = env('SUPABASE_BUCKET');

        $fileName = basename($url);

        $path = $bucket.'/'.$fileName;

        $deleteUrl = env('SUPABASE_URL').'/storage/v1/object/'.$path;

        Http::withHeaders([
            'apikey' => env('SUPABASE_KEY'),
            'Authorization' => 'Bearer '.env('SUPABASE_KEY'),
        ])->delete($deleteUrl);
    }
}
