<?php

namespace Modules\Education\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Modules\Education\Services\TeacherService;
use Illuminate\Http\Request;
use Modules\Education\Transformers\TeacherDetailResource;
use Modules\Education\Transformers\TeacherListResource;

class TeacherController extends Controller
{
    protected $service;

    public function __construct(TeacherService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $teachers = $this->service->getTeachersList();
        return ApiResponse::success(TeacherListResource::collection($teachers), 'تم جلب قائمة المعلمين');
    }

    public function show($id)
    {
        $teacher = $this->service->getTeacherDetails($id);
        return ApiResponse::success(new TeacherDetailResource($teacher), 'تم جلب تفاصيل المعلم');
    }
}
