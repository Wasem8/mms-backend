<?php

namespace Modules\Education\Http\Controllers;

use Illuminate\Http\Request;
use App\Support\ApiResponse;
use Modules\Education\Http\Requests\AttachStudentsRequest;
use Modules\Education\Http\Requests\StoreHalaqaRequest;
use Modules\Education\Http\Requests\UpdateHalaqaRequest;
use Modules\Education\Services\HalaqaService;
use Modules\Education\Transformers\HalaqaResource;

class HalaqaController
{

    public function __construct(protected HalaqaService $service)
    {
    }
    public function index()
    {
        $halaqat = $this->service->list();
        return ApiResponse::success(
            HalaqaResource::collection($halaqat),
            __('messages.halaqat_retrieved'),
            ApiResponse::pagination($halaqat)
        );
    }

    public function store(StoreHalaqaRequest $request)
    {
        $halaqa = $this->service->create($request->validated());
        return ApiResponse::success(new HalaqaResource($halaqa), __('messages.halaqa_created'));
    }

    public function show($id)
    {
        $halaqa = $this->service->find($id, ['students', 'teacher', 'mosque']);
        return ApiResponse::success(new HalaqaResource($halaqa), __('messages.halaqa_details'));
    }

    public function update(UpdateHalaqaRequest $request, $id)
    {
        $halaqa = $this->service->update($id, $request->validated());
        return ApiResponse::success(new HalaqaResource($halaqa), __('messages.halaqa_updated'));
    }

    public function destroy($id)
    {
        $this->service->delete($id);
        return ApiResponse::success([], __('messages.halaqa_deleted'));
    }

    public function attachStudents(AttachStudentsRequest $request, $id)
    {
        $this->service->attachStudents($id, $request->validated('students'));
        return ApiResponse::success([], __('messages.students_attached'));
    }

    public function detachStudent($id, $studentId)
    {
        $this->service->detachStudent($id, $studentId);
        return ApiResponse::success([], __('messages.student_detached'));
    }
}
