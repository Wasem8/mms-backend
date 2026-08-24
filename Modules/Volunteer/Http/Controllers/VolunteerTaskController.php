<?php

namespace Modules\Volunteer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Modules\Volunteer\Http\Requests\AssignTaskRequest;
use Modules\Volunteer\Http\Requests\CreateTaskRequest;
use Modules\Volunteer\Services\VolunteerTaskService;

class VolunteerTaskController extends Controller
{
    public function __construct(
        private readonly VolunteerTaskService $service,
    ) {}

    /** Manager: list all tasks (assigned + unassigned) for an opportunity */
    public function index(string $opportunityId)
    {
        $tasks = $this->service->listForOpportunity((int) $opportunityId);
        $tasks->each->append('volunteer_name');
        return ApiResponse::success($tasks, __('messages.tasks_retrieved'), 200);
    }

    /** Manager: create a general, unassigned task for an opportunity */
    public function store(CreateTaskRequest $request, string $opportunityId)
    {
        $task = $this->service->create($request->toDTO((int) $opportunityId));
        return ApiResponse::success($task, __('messages.task_created'), 201);
    }

    /** Manager: distribute an existing task to one approved volunteer */
    public function assign(AssignTaskRequest $request, string $taskId)
    {
        $dto  = $request->toDTO((int) $taskId);
        $task = $this->service->assign($dto->taskId, $dto->applicationId);

        return ApiResponse::success($task, __('messages.task_assigned'), 200);
    }

    /** Volunteer: list tasks assigned to my own approved application */
    public function applicationTasks(string $applicationId)
    {
        $tasks = $this->service->listForApplication((int) $applicationId, (int) auth()->id());
        return ApiResponse::success($tasks, __('messages.tasks_retrieved'), 200);
    }

    /** Volunteer: mark my own assigned task as completed */
    public function complete(string $taskId)
    {
        $task = $this->service->complete((int) $taskId, (int) auth()->id());
        return ApiResponse::success($task, __('messages.task_completed'), 200);
    }
}
