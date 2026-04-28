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
    public function __construct(private HalaqaService $service) {}

    public function index()
    {
        $halaqat = $this->service->list();

        return ApiResponse::success(
            HalaqaResource::collection($halaqat),
            'تم جلب الحلقات بنجاح',
            ApiResponse::pagination($halaqat)
        );
    }

    public function store(StoreHalaqaRequest $request)
    {
        $halaqa = $this->service->create($request->validated());

        return ApiResponse::success(new HalaqaResource($halaqa), 'تم إنشاء الحلقة بنجاح');
    }

    public function show($id)
    {
        return ApiResponse::success(
            $this->service->find($id),
            'تم جلب تفاصيل الحلقة'
        );
    }

    public function update(UpdateHalaqaRequest $request, $id)
    {
        $halaqa = $this->service->update($id, $request->validated());

        return ApiResponse::success($halaqa, 'تم تحديث بيانات الحلقة بنجاح');
    }

    public function destroy($id)
    {
        $this->service->delete($id);

        return ApiResponse::success([], 'تم حذف الحلقة بنجاح');
    }

    public function attachStudents(AttachStudentsRequest $request, $id)
    {
        $this->service->attachStudents(
            $id,
            $request->validated('students')
        );

        return ApiResponse::success([], 'تم إضافة الطلاب إلى الحلقة بنجاح');
    }

    public function detachStudent($id, $studentId)
    {
        $this->service->detachStudent($id, $studentId);

        return ApiResponse::success([], 'تم إزالة الطالب من الحلقة');
    }
}
