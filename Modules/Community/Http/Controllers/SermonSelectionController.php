<?php

namespace Modules\Community\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Modules\Community\Http\Requests\SelectSermonForFridayRequest;
use Modules\Community\Http\Resources\SermonSelectionResource;
use Modules\Community\Services\SermonSelectionService;
use OpenApi\Attributes as OA;

class SermonSelectionController extends Controller
{
    public function __construct(protected SermonSelectionService $service) {}


    public function store(SelectSermonForFridayRequest $request)
    {
        $selection = $this->service->selectForFriday($request->toDTO(), $request->user());

        return ApiResponse::success(
            new SermonSelectionResource($selection),
            'تم اختيار الخطبة بنجاح لإلقائها يوم الجمعة.'
        );
    }

    public function mine(Request $request)
    {
        $filters = $request->validate([
            'friday_date_from' => ['nullable', 'date'],
            'friday_date_to' => ['nullable', 'date'],
        ]);

        $selections = $this->service->getMySelections($request->user(), $filters);

        return ApiResponse::success(
            SermonSelectionResource::collection($selections->items()),
            'تم جلب اختياراتك بنجاح.',
            $selections
        );
    }

    public function upcoming(Request $request)
    {
        $selections = $this->service->getUpcomingPerMosque();

        return ApiResponse::success(
            SermonSelectionResource::collection($selections),
            'تم جلب الخطبة المختارة لكل مسجد بنجاح.'
        );
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'mosque_manager_id' => ['nullable', 'integer'],
            'friday_date_from' => ['nullable', 'date'],
            'friday_date_to' => ['nullable', 'date'],
        ]);

        $selections = $this->service->searchSelections($filters);

        return ApiResponse::success(
            SermonSelectionResource::collection($selections->items()),
            'تم جلب سجل الاختيارات بنجاح.',
            $selections
        );
    }

    public function destroy(int $id, Request $request)
    {
        $this->service->cancelSelection($id, $request->user());
        return ApiResponse::success(null, 'تم إلغاء اختيار الخطبة بنجاح.');
    }
}
