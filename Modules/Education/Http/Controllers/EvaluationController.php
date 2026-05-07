<?php

namespace Modules\Education\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Modules\Education\Services\EvaluationService;
use Modules\Education\Http\Requests\StoreEvaluationRequest;
use Modules\Education\Transformers\EvaluationResource;

class EvaluationController
{
    public function __construct(private EvaluationService $service) {}

    public function store(StoreEvaluationRequest $request, EvaluationService $service)
    {
        $data = $request->validated([]);

        $evaluation = $service->store($data);

        return ApiResponse::success(
            $evaluation,
            'تم تقييم الطالب بنجاح'
        );
    }

    public function indexForSupervisor(Request $request)
    {
        $data = $this->service->getMosqueEvaluations(auth()->user()->mosque_id, $request->all());
        return ApiResponse::success(EvaluationResource::collection($data), 'تم جلب البيانات بنجاح', ApiResponse::pagination($data));
    }

    public function indexForTeacher(Request $request)
    {
        $data = $this->service->getTeacherEvaluations(auth()->id(), $request->all());
        return ApiResponse::success(EvaluationResource::collection($data), 'تم جلب البيانات بنجاح', ApiResponse::pagination($data));
    }

    public function indexForParent(Request $request)
    {
        $data = $this->service->getParentEvaluations(auth()->id(), $request->all());
        return ApiResponse::success(EvaluationResource::collection($data), 'تم جلب البيانات بنجاح', ApiResponse::pagination($data));
    }

    public function show($id)
    {
        $evaluation = $this->service->getEvaluationById($id);
        return ApiResponse::success(new EvaluationResource($evaluation),'تم جلب البيانات بنجاح');
    }
}
