<?php

namespace Modules\Education\Http\Controllers;

use Illuminate\Http\Request;
use App\Support\ApiResponse;
use Modules\Education\Http\Requests\StoreStudentRequest;
use Modules\Education\Services\StudentService;
use Modules\Education\Transformers\StudentResource;

class StudentController
{
    public function __construct(private StudentService $service) {}

    public function index()
    {
        $students = $this->service->list();
        return ApiResponse::success(
            StudentResource::collection($students),
            'تم استعادة قائمة الطلاب بنجاح.',
            ApiResponse::pagination($students)
        );
    }

    public function store(StoreStudentRequest $request)
    {
        $student = $this->service->create($request->validated());

        return ApiResponse::success(
            new StudentResource($student),
            'تم تسجيل بيانات الطالب بنجاح، يرجى انتظار موافقة مشرف الحلقات لتفعيل الحساب.'
        );
    }

    public function show($id)
    {
        $student = $this->service->find($id);

        return ApiResponse::success(
            new StudentResource($student), // هنا نستخدم الـ Resource
            'تم جلب بيانات الطالب بنجاح.'
        );
    }

    public function update(Request $request, $id)
    {
        return ApiResponse::success(
            $this->service->update($id, $request->all()),
            'تم تحديث بيانات الطالب بنجاح.'
        );
    }

    public function destroy($id)
    {
        $this->service->delete($id);

        return ApiResponse::success([], 'تم حذف سجل الطالب بنجاح.');
    }
}
