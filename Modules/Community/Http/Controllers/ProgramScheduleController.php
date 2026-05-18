<?php

namespace Modules\Community\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Modules\Community\Services\ProgramScheduleService;
use Modules\Community\Http\Requests\StoreProgramScheduleRequest;
use Modules\Community\Http\Requests\UpdateProgramScheduleRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgramScheduleController extends Controller
{
    public function __construct(protected ProgramScheduleService $service) {}

    /**
     * GET /dawah_programs/{program}/schedules
     * List all schedules for a program (public).
     */
    public function index(Request $request, int $program): JsonResponse
    {
        $filters   = $request->only(['date', 'from_date', 'to_date', 'per_page']);
        $schedules = $this->service->getSchedulesByProgram($program, $filters);

        return response()->json([
            'message' => 'Schedules retrieved successfully.',
            'data'    => $schedules,
        ]);
    }

    /**
     * GET /dawah_programs/{program}/schedules/{schedule}
     * Show a single schedule (public).
     */
    public function show(int $program, int $schedule): JsonResponse
    {
        $schedule = $this->service->getScheduleById($program, $schedule);

        return response()->json([
            'message' => 'Schedule retrieved successfully.',
            'data'    => $schedule,
        ]);
    }

    /**
     * POST /mosques/{mosque}/dawah_programs/{program}/schedules
     * Create a new schedule (mosque_manager only).
     */
    public function store(StoreProgramScheduleRequest $request, int $mosque, int $program): JsonResponse
    {
        $schedule = $this->service->createSchedule($program, $request->validated());

        return ApiResponse::success($schedule,'تم إضافة محاصرة جديدة للبرنامج');
    }

    /**
     * PUT /mosques/{mosque}/dawah_programs/{program}/schedules/{schedule}
     * Update a schedule (mosque_manager only).
     */
    public function update(UpdateProgramScheduleRequest $request, int $mosque, int $program, int $schedule): JsonResponse
    {
        $schedule = $this->service->updateSchedule($program, $schedule, $request->validated());

        return ApiResponse::success($schedule,'تم التعديل بنجاح');
    }

  
    public function destroy(int $mosque, int $program, int $schedule): JsonResponse
    {
        $this->service->deleteSchedule($program, $schedule);

        return ApiResponse::success('تم الحذف بنجاح');
    }
}
