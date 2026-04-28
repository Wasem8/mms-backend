<?php

namespace Modules\Education\Http\Controllers;

use Illuminate\Http\Request;
use App\Support\ApiResponse;
use Modules\Education\Services\StudentService;

class StudentController
{
    public function __construct(private StudentService $service) {}

    public function index()
    {
        return ApiResponse::success(
            $this->service->list(),
            'Students retrieved.'
        );
    }

    public function store(Request $request)
    {
        $student = $this->service->create($request->all());

        return ApiResponse::success($student, 'Created.');
    }

    public function show($id)
    {
        return ApiResponse::success(
            $this->service->find($id),
            'Student details.'
        );
    }

    public function update(Request $request, $id)
    {
        return ApiResponse::success(
            $this->service->update($id, $request->all()),
            'Updated.'
        );
    }

    public function destroy($id)
    {
        $this->service->delete($id);

        return ApiResponse::success([], 'Deleted.');
    }
}
