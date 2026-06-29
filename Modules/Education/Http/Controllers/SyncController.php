<?php

namespace Modules\Education\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Modules\Education\Services\Sync\SyncService;

class SyncController extends Controller
{
    public function __construct(
        private SyncService $syncService
    ) {}

    public function sync(Request $request)
    {
        $request->validate([
            'ops' => 'required|array',
            'ops.*.type' => 'required|string|in:attendance,evaluation,excuse_decision,evaluation_update,evaluation_delete',
            'ops.*.client_uuid' => 'required|uuid',
            'ops.*.data' => 'required|array',
        ]);

        $results = $this->syncService->sync(
            $request->input('ops')
        );

        return ApiResponse::success(
            $results,
            'تمت مزامنة العمليات بنجاح'
        );
    }
}
