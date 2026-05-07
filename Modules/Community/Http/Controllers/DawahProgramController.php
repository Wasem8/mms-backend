<?php

namespace Modules\Community\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Modules\Community\Http\Requests\StoreDawahProgramRequest;
use Modules\Community\Http\Requests\UpdateDawahProgramRequest;
use Modules\Community\Services\DawahProgramService;
use Modules\Community\Models\DawahProgram;
use Modules\Mosque\Models\Mosque;

class DawahProgramController extends Controller
{
    protected $dawahProgramService;

    public function __construct(DawahProgramService $dawahProgramService)
    {
        $this->dawahProgramService = $dawahProgramService;
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $programs = $this->dawahProgramService->getAllPrograms($perPage);

        return ApiResponse::success($programs, 'تم جلب البرامج بنجاح');
    }

    public function show(Mosque $mosque, DawahProgram $program)
    {
        return ApiResponse::success($program, 'تم جلب البرنامج بنجاح');
    }
    public function store(StoreDawahProgramRequest $request, Mosque $mosque)
    {
        try {
            $data = $request->validated();
            $data['mosque_id'] = $mosque->id;

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image');
            }

            $program = $this->dawahProgramService->createProgram($data);

            return ApiResponse::success($program, 'تم إنشاء البرنامج بنجاح', 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function update(UpdateDawahProgramRequest $request, Mosque $mosque, DawahProgram $program)
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image');
            }

            $updatedProgram = $this->dawahProgramService->updateProgram($program, $data);

            return ApiResponse::success($updatedProgram, 'تم تحديث البرنامج بنجاح');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function destroy(Mosque $mosque, DawahProgram $program)
    {
        try {
            $this->dawahProgramService->deleteProgram($mosque, $program);
            return ApiResponse::success(null, 'تم حذف البرنامج بنجاح');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    public function getProgramsByMosque($mosqueId)
    {
        $programs = $this->dawahProgramService->getProgramsByMosque($mosqueId);

        return ApiResponse::success($programs);
    }
}
