<?php

namespace Modules\Volunteer\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Volunteer\DTOs\AssignTaskDTO;
use Modules\Volunteer\Http\Requests\AssignTaskRequest;
use Modules\Volunteer\Services\VolunteerTaskService;
use App\Support\ApiResponse;

class VolunteerTaskController extends Controller
{
    public function __construct(
        private readonly VolunteerTaskService $service,
    ) {}

    /** Manager / Volunteer: list tasks for an application */
    public function index(string $applicationId)
    {
       $tasks = $this->service->listForApplication((int) $applicationId);
       return ApiResponse::success($tasks, 'Tasks retrieved successfully.', 200);
    }

    /** Manager: assign a new task */
    public function store(AssignTaskRequest $request)
    {
        $task = $this->service->assign($request->toDTO());
        return ApiResponse::success($task, 'Task assigned successfully.', 201);
    }

    /** Volunteer: mark their own task as completed */
    public function complete(string $taskId)
    {
        $task = $this->service->markCompleted((int) $taskId);
        return ApiResponse::success($task, 'Task marked as completed successfully.', 200);
    }
}
