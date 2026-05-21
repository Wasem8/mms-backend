<?php

namespace Modules\Education\Http\Controllers;

use Illuminate\Http\Request;
use App\Support\ApiResponse;
use Modules\Education\Http\Requests\StoreStudentRequest;
use Modules\Education\Http\Requests\UpdateStudentRequest;
use Modules\Education\Services\StudentService;
use Modules\Education\Transformers\StudentDetailResource;
use Modules\Education\Transformers\StudentResource;
use Modules\Education\Transformers\StudentTransferResource;

class StudentController
{
    public function __construct(private StudentService $service) {}

    public function index()
    {
        $students = $this->service->list();
        return ApiResponse::success(
            StudentResource::collection($students),
            __('messages.students_retrieved'),
            ApiResponse::pagination($students)
        );
    }

    public function store(StoreStudentRequest $request)
    {
        $student = $this->service->create($request->validated());

        return ApiResponse::success(
            new StudentResource($student),
            __('messages.student_stored')
        );
    }

    public function show($id)
    {
        $student = $this->service->find($id);

        return ApiResponse::success(
            new StudentDetailResource($student),
            __('messages.student_retrieved')
        );
    }

    public function search(Request $request)
    {
        $students = $this->service->search($request->all());
        return ApiResponse::success(
            StudentResource::collection($students),
            __('messages.students_retrieved'),
            ApiResponse::pagination($students)
        );
    }


    public function update(UpdateStudentRequest $request, $id)
    {
        $student = $this->service->update($id, $request->validated());

        return ApiResponse::success(
            new StudentResource($student),
            __('messages.student_updated')
        );
    }


    public function destroy($id)
    {
        $this->service->delete($id);

        return ApiResponse::success([], __('messages.student_deleted'));
    }


    public function approve(Request $request, $id)
    {
        $validated = $request->validate([
            'halaqa_id' => 'nullable|integer|exists:halaqats,id'
        ]);

        $result = $this->service->approve($id, $validated);

        if (isset($result['error']) && $result['error']) {
            return ApiResponse::error($result['message'], 400);
        }

        return ApiResponse::success(
            new StudentResource($result['data']),
            __('messages.student_approved_and_assigned')
        );
    }

    public function reject($id)
    {
        $result = $this->service->reject($id);

        if (isset($result['error']) && $result['error']) {
            return ApiResponse::error($result['message'], 400);
        }

        return ApiResponse::success(
            new StudentResource($result['data']),
            __('messages.student_rejected')
        );
    }

    public function transfer(Request $request, $id)
    {
        $request->validate([
            'from_halaqa_id' => 'required|exists:halaqats,id',
            'to_halaqa_id'   => 'required|exists:halaqats,id|different:from_halaqa_id',
        ]);

        $result = $this->service->transferHalaqa($id, $request->all());

        if ($result['error']) {
            return ApiResponse::error($result['message']);
        }

        return ApiResponse::success(
            new StudentTransferResource($result['data']),
            $result['message']
        );
    }
}
