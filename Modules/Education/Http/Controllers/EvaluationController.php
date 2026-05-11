<?php

namespace Modules\Education\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Modules\Education\Http\Requests\UpdateEvalutionRequest;
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
            __('messages.evaluation_retrieved'),
        );
    }

    public function indexForSupervisor(Request $request)
    {
        $data = $this->service->getMosqueEvaluations(auth()->user()->mosque_id, $request->all());
        return ApiResponse::success(EvaluationResource::collection($data), __('messages.evaluation_retrieved'), ApiResponse::pagination($data));
    }

    public function indexForTeacher(Request $request)
    {
        $data = $this->service->getTeacherEvaluations(auth()->id(), $request->all());
        return ApiResponse::success(EvaluationResource::collection($data), __('messages.evaluation_retrieved'), ApiResponse::pagination($data));
    }

    public function indexForParent(Request $request)
    {
        $data = $this->service->getParentEvaluations(auth()->id(), $request->all());
        return ApiResponse::success(EvaluationResource::collection($data), __('messages.evaluation_retrieved'), ApiResponse::pagination($data));
    }

    public function show($id)
    {
        $evaluation = $this->service->getEvaluationById($id);
        return ApiResponse::success(new EvaluationResource($evaluation),__('messages.evaluation_stored'));
    }

    public function update(UpdateEvalutionRequest $request, $id)
    {
        $data = $request->validated([]);

        $evaluation = $this->service->update($id, $data);

        return ApiResponse::success(
            new EvaluationResource($evaluation),
            __('messages.evaluation_updated')
        );
    }


    public function destroy($id)
    {
        try {
            $this->service->delete($id);
            return ApiResponse::success(null, __('messages.evaluation_deleted'));
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 403);
        }
    }
}
