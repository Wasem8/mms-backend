<?php

namespace Modules\Mosque\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Modules\Mosque\Http\Resources\MosqueTaskResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Mosque\Models\MosqueTask;
use Modules\Mosque\Http\Requests\StoreMosqueTaskRequest;
use Modules\Mosque\Http\Requests\UpdateMosqueTaskRequest;
use Modules\Mosque\Models\Mosque;
use Modules\Mosque\Services\MosqueTaskService;

class MosqueTaskController extends Controller
{
    public function __construct(private readonly MosqueTaskService $service) {}

    public function index(Request $request)
    {
        $mosqueId = Mosque::managedBy(Auth::id())->firstOrFail()->id;
        $date = $request->query('date', now()->toDateString());
        $status = $request->query('status');
        $category = $request->query('category');

        $result = $this->service->listForDate($mosqueId, $date, $status, $category);

        return ApiResponse::success([
            'tasks'   => MosqueTaskResource::collection($result['tasks']),
            'summary' => [
                'total'       => $result['total'],
                'completed'   => $result['completed'],
                'percentage'  => $result['percentage'],
                'by_category' => $result['by_category'],
            ],
        ], __('messages.mosque_tasks_retrieved'));
    }

    public function dateTabs()
    {
        $mosqueId = Mosque::managedBy(Auth::id())->firstOrFail()->id;

        return ApiResponse::success(
            $this->service->dateTabs($mosqueId),
            __('messages.mosque_task_date_tabs_retrieved')
        );
    }

    public function store(StoreMosqueTaskRequest $request)
    {
        $task = $this->service->create($request->toDTO());

        return ApiResponse::success(new MosqueTaskResource($task), __('messages.mosque_task_created'));
    }

    public function update(UpdateMosqueTaskRequest $request, MosqueTask $task)
    {
        $this->authorizeTaskAccess($task);

        $task = $this->service->update($task, $request->toDTO());

        return ApiResponse::success(new MosqueTaskResource($task), __('messages.mosque_task_updated'));
    }

    public function toggleComplete(MosqueTask $task)
    {
        $this->authorizeTaskAccess($task);

        $task = $this->service->toggleComplete($task);

        return ApiResponse::success(new MosqueTaskResource($task), __('messages.mosque_task_status_updated'));
    }

    public function destroy(MosqueTask $task)
    {
        $this->authorizeTaskAccess($task);

        $this->service->delete($task);

        return ApiResponse::success(null, __('messages.mosque_task_deleted'));
    }

    private function authorizeTaskAccess(MosqueTask $task): void
    {
        abort_unless(
            Mosque::managedBy(Auth::id())->where('id', $task->mosque_id)->exists(),
            403
        );
    }
}
