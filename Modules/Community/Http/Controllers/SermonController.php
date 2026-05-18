<?php

namespace Modules\Community\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Modules\Community\Services\SermonService;

class SermonController extends Controller
{
    protected $sermonService;

    public function __construct(SermonService $sermonService)
    {
        $this->sermonService = $sermonService;
    }

    public function index()
    {
        $sermons = $this->sermonService->getAllSermons();
        return response()->json(['message' => 'تم جلب جميع الخطب بنجاح', 'data' => $sermons]);
    }

    public function pending()
    {
        $sermons = $this->sermonService->getPendingSermons();
        return response()->json(['message' => 'تم جلب الخطب المعلقة بنجاح', 'data' => $sermons]);
    }

    public function show($id)
    {
        $sermon = $this->sermonService->getSermionById($id);
        if (! $sermon) {
            return ApiResponse::error('خطبة غير موجودة');
        }
        return ApiResponse::success($sermon, 'تم جلب الخطبة بنجاح');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'attachments' => 'sometimes|array',
            'attachments.*' => 'file|max:10240',
        ]);

        $attachments = $request->file('attachments', []);
        if ($attachments && !is_array($attachments)) {
            $attachments = [$attachments];
        }

        $mosqueManagerId = auth()->id();

        $sermon = $this->sermonService->uploadSermon($validatedData, $mosqueManagerId, $attachments);

        return ApiResponse::success(['message' => 'تم رفع الخطبة بنجاح وبانتظار الاعتماد', 'data' => $sermon], 201);
    }


    public function approve(Request $request, $id)
    {
        $notes = $request->input('notes');
        $regionManagerId = auth()->id();

        $sermon = $this->sermonService->approveSermon($id, $regionManagerId, $notes);

        return ApiResponse::success(['message' => 'تم اعتماد الخطبة بنجاح', 'data' => $sermon]);
    }

    public function reject(Request $request, $id)
    {
        $notes = $request->input('notes');
        $regionManagerId = auth()->id();

        $sermon = $this->sermonService->rejectSermon($id, $regionManagerId, $notes);

        return ApiResponse::success(['message' => 'تم رفض الخطبة بنجاح', 'data' => $sermon]);
    }

    public function checkCompleted()
    {
        $updatedCount = $this->sermonService->markPastSermonsAsCompleted();

        return ApiResponse::success([
            'message' => "تم التحقق بنجاح. تم تحديث حالة {$updatedCount} خطبة إلى مكتملة.",
            'updated_count' => $updatedCount
        ]);
    }

}
