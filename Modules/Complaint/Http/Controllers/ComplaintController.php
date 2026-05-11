<?php

namespace Modules\Complaint\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Modules\Complaint\Http\Requests\StoreComplaintRequest;
use Modules\Complaint\Http\Requests\UpdateComplaintRequest;
use Modules\Complaint\Service\ComplaintService;

class ComplaintController extends Controller
{

    protected $complaintService;

    public function __construct(ComplaintService $complaintService)
    {
        $this->complaintService = $complaintService;
    }

    public function index()
    {
        $complaints = $this->complaintService->all();
        return ApiResponse::success($complaints);
    }

    public function show($id)
    {
        $complaint = $this->complaintService->find($id);
        return ApiResponse::success($complaint);
    }

    public function store(StoreComplaintRequest $request)
    {
        $data = $request->validated();

        $complaint = $this->complaintService->create($data);
        return ApiResponse::success($complaint, 'Complaint created successfully', 201);
    }

    public function update(UpdateComplaintRequest $request, $id)
    {
        $data = $request->validated();

        $complaint = $this->complaintService->update($id, $data);
        return ApiResponse::success($complaint, 'Complaint updated successfully');
    }

    public function destroy($id)
    {
        $this->complaintService->delete($id);
        return ApiResponse::success(null, 'Complaint deleted successfully');
    }
}
