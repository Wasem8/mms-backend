<?php

namespace Modules\Education\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Modules\Education\Http\Requests\UpdateTeacherRequest;
use Modules\Education\Services\TeacherService;
use Illuminate\Http\Request;
use Modules\Education\Transformers\TeacherDetailResource;
use Modules\Education\Transformers\TeacherListResource;
use Modules\Education\Transformers\TeacherResource;

class TeacherController extends Controller
{
    protected $service;

    public function __construct(TeacherService $service)
    {
        $this->service = $service;
    }

    public function update(UpdateTeacherRequest $request, $id)
    {
        $teacher = $this->service->updateTeacher($id, $request->validated());

        return ApiResponse::success(
            new TeacherResource($teacher),
            __('messages.teacher_updated_successfully')
        );
    }
    public function index()
    {
        $teachers = $this->service->getTeachersList();
        return ApiResponse::success(TeacherResource::collection($teachers), 'تم جلب قائمة المعلمين');
    }

    public function show($id)
    {
        $teacher = $this->service->getTeacherDetails($id);
        return ApiResponse::success(new TeacherDetailResource($teacher), 'تم جلب تفاصيل المعلم');
    }
}
