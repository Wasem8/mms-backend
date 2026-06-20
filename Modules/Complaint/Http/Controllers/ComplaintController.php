<?php

namespace Modules\Complaint\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Complaint\Http\Requests\SubmitComplaintRequest as RequestsSubmitComplaintRequest;
use Modules\Complaint\Service\ComplaintService;
use Modules\Complaint\Http\Requests\UpdateComplaintRequest;
use Modules\Mosque\Models\Mosque;

class ComplaintController extends Controller
{
    public function __construct(
        protected ComplaintService $service
    ) {}

    private function getManagerMosqueId(): ?int
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('mosque_manager')) {
            return null;
        }

        $mosque = Mosque::where('manager_id', $user->id)->first();

        if (!$mosque) {
            abort(403, __('messages.complaint.no_mosque_assigned'));
        }

        return $mosque->id;
    }

    public function storeGuest(
        RequestsSubmitComplaintRequest $request
    ) {
        try {

            $data = $request->validated();

            $files =
                $request->file('files');

            $files =
                is_array($files)
                    ? $files
                    : ($files ? [$files] : []);

            $complaint =
                $this->service
                    ->submitComplaint(
                        $data,
                        $files
                    );

            return response()->json($complaint);

        } catch (\Throwable $e) {

            Log::error(
                'Complaint upload failed',
                [
                    'message' =>
                        $e->getMessage(),

                    'line' =>
                        $e->getLine(),

                    'file' =>
                        $e->getFile(),

                    'trace' =>
                        $e->getTraceAsString(),
                ]
            );

            throw $e;
        }
    }

    public function storeMember(RequestsSubmitComplaintRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        $files = $request->file('files');
        $files = is_array($files) ? $files : ($files ? [$files] : []);

        $complaint = $this->service->submitComplaint($data, $files);
        return ApiResponse::success($complaint, __('messages.complaint.submitted_member'));
    }
    public function track($complaintNumber)
    {
        $complaint = $this->service->trackComplaint($complaintNumber);

        $history = collect([[
            'status' => 'pending',
            'date' => $complaint->created_at,
            'note' => __('messages.complaint.submitted_member'),
        ]]);

        $logs = $complaint->statusLogs->map(function ($log) {
            return [
                'status' => $log->new_status,
                'date' => $log->changed_at,
                'note' => $log->note
            ];
        });

        $fullHistory = $history->concat($logs);

        return ApiResponse::success([
            'complaint_number' => $complaint->complaint_number,
            'title' => $complaint->title,
            'current_status' => $complaint->status,
            'admin_resolution_note' => $complaint->admin_notes,
            'created_at' => $complaint->created_at,
            'status_history' => $fullHistory
        ], __('messages.complaint.tracked'));
    }

    public function recentComplaints()
    {
        $mosqueId = $this->getManagerMosqueId();

        $data = $this->service->getRecentComplaints($mosqueId);

        return response()->json([
            'status'  => true,
            'message' => __('messages.complaint.retrieved'),
            'data'    => $data,
        ]);
    }


    public function search(Request $request)
    {
        $validated = $request->validate([
            'q'           => ['required', 'string', 'min:1'],
            'per_page'    => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $filters = ['search' => $validated['q']];

        $mosqueId = $this->getManagerMosqueId();
        if ($mosqueId) {
            $filters['mosque_id'] = $mosqueId;
        } elseif ($request->has('mosque_id')) {
            $filters['mosque_id'] = $request->mosque_id;
        }

        $filters['per_page'] = (int) ($validated['per_page'] ?? 15);
        $complaints = $this->service->getComplaintsForAdmin($filters);

        return ApiResponse::success($complaints->items(), __('messages.complaint.retrieved'), $complaints);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'complaint_type', 'priority', 'per_page']);

        $mosqueId = $this->getManagerMosqueId();
        if ($mosqueId) {
            $filters['mosque_id'] = $mosqueId;
        } elseif ($request->has('mosque_id')) {
            $filters['mosque_id'] = $request->mosque_id;
        }

        $complaints = $this->service->getComplaintsForAdmin($filters);

        return ApiResponse::success($complaints->items(), __('messages.complaint.retrieved'), $complaints);
    }

    public function updateStatus(UpdateComplaintRequest $request, $id)
    {
        $validated = $request->validated();

        $complaint = $this->service->updateStatus(
            $id,
            $validated['status'],
            auth()->id(),
            $validated['note'] ?? null
        );

        return ApiResponse::success($complaint, __('messages.complaint.status_updated'));
    }

    public function show($id)
    {
        $filters = [];

        $mosqueId = $this->getManagerMosqueId();
        if ($mosqueId) {
            $filters['mosque_id'] = $mosqueId;
        }

        $complaint = $this->service->getComplaintDetails((int)$id, $filters);

        return ApiResponse::success($complaint, __('messages.complaint.details_retrieved'));
    }

    public function statistics(Request $request)
    {
        $filters = [];

        $mosqueId = $this->getManagerMosqueId();
        if ($mosqueId) {
            $filters['mosque_id'] = $mosqueId;
        } elseif ($request->has('mosque_id')) {
            $filters['mosque_id'] = $request->mosque_id;
        }

        $stats = $this->service->getComplaintStatistics($filters);

        return ApiResponse::success($stats, __('messages.complaint.statistics_retrieved'));
    }

    public function pageStats()
    {
        $mosqueId = $this->getManagerMosqueId();

        $data = $this->service->getComplaintPageStats($mosqueId);

        return response()->json([
            'status'  => true,
            'message' => __('messages.complaint.retrieved'),
            'data'    => $data,
        ]);
    }
}
