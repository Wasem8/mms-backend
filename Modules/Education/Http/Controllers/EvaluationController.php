<?php

namespace Modules\Education\Http\Controllers;

use App\Support\ApiResponse;
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

    public function index()
    {
        $data = $this->service->list();

        return ApiResponse::success(
            EvaluationResource::collection($data),
            'تم جلب التقييمات بنجاح',
            ApiResponse::pagination($data)
        );
    }
}
