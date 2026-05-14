<?php

namespace Modules\Complaint\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Modules\Complaint\Http\Requests\SubmitComplaintRequest as RequestsSubmitComplaintRequest;
use Modules\Complaint\Service\ComplaintService;
use Modules\Complaint\Http\Requests\UpdateComplaintRequest;

class ComplaintController extends Controller
{
    public function __construct(
        protected ComplaintService $service
    ) {}
    public function storeGuest(RequestsSubmitComplaintRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = null;

        $files = $request->file('files');
        $files = is_array($files) ? $files : ($files ? [$files] : []);

        $complaint = $this->service->submitComplaint($data, $files);
        return ApiResponse::success($complaint, 'تم تقديم الشكوى بنجاح.');
    }

    public function storeMember(RequestsSubmitComplaintRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        $files = $request->file('files');
        $files = is_array($files) ? $files : ($files ? [$files] : []);

        $complaint = $this->service->submitComplaint($data, $files);
        return ApiResponse::success($complaint, 'تم تقديم الشكوى بنجاح.');
    }
    public function track($complaintNumber)
    {
        $complaint = $this->service->trackComplaint($complaintNumber);

        // 1. نبدأ بسجل الحالة الابتدائية (عند الإنشاء)
        $history = collect([[
            'status' => 'pending', // أو الحالة الابتدائية التي تبدأ بها الشكوى
            'date' => $complaint->created_at,
            'note' => 'تم تقديم الشكوى بنجاح' // ملاحظة افتراضية
        ]]);

        // 2. نضيف سجلات التغيير التي قمت بتسجيلها في logStatusChange
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
        ], 'Complaint status retrieved successfully.');
    }
    // ==========================================
    // ADMIN & MANAGER METHODS
    // ==========================================

    /**
     * GET: List complaints with RBAC filtering (Mosque Manager / Region Manager)
     */
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'mosque_id', 'complaint_type', 'priority']);

        $user = auth()->user();

        // RBAC Enforcement: Mosque Managers only see their own mosque
        if ($user && $user->role === 'mosque_manager') {
            $filters['mosque_id'] = $user->mosque_id;
        }

        $complaints = $this->service->getComplaintsForAdmin($filters);

        return ApiResponse::success($complaints, 'Complaints retrieved successfully.');
    }

    /**
     * PATCH: Update status and add resolution notes (Mosque Manager / Region Manager)
     */
    public function updateStatus(UpdateComplaintRequest $request, $id)
    {
        $validated = $request->validated();

        $complaint = $this->service->updateStatus(
            $id,
            $validated['status'],
            auth()->id(),
            $validated['note'] ?? null
        );

        return ApiResponse::success($complaint, 'Complaint status updated successfully.');
    }

    public function show($id)
    {
        $user = auth()->user();
        $filters = [];

        if ($user && $user->role === 'mosque_manager') {
            $filters['mosque_id'] = $user->mosque_id;
        }

        $complaint = $this->service->getComplaintDetails((int)$id, $filters);

        return ApiResponse::success($complaint, 'Complaint details retrieved successfully.');
    }

    public function statistics(Request $request)
    {
        $user = auth()->user();
        $filters = [];

        if ($user && $user->role === 'mosque_manager') {
            $filters['mosque_id'] = $user->mosque_id;
        } elseif ($user && $user->role === 'super_admin' && $request->has('mosque_id')) {
            $filters['mosque_id'] = $request->mosque_id;
        }

        $stats = $this->service->getComplaintStatistics($filters);

        return ApiResponse::success($stats, 'تم استرجاع الإحصائيات بنجاح');
    }
}
