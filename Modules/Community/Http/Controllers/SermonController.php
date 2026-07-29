<?php

namespace Modules\Community\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Modules\Community\Http\Requests\CreateSermon;
use Modules\Community\Services\SermonService;
use Modules\Community\Http\Requests\SearchSermonRequest;


class SermonController extends Controller
{
    protected $sermonService;

    public function __construct(SermonService $sermonService)
    {
        $this->sermonService = $sermonService;
    }

    public function store(CreateSermon $request)
    {
        $validatedData = $request->validated();

        $files = $request->file('attachments') ?? [];

        $sermon = $this->sermonService->createSermon($validatedData, auth()->id(), $files);

        return ApiResponse::success($sermon, 'Sermon submitted successfully with its attachments and is pending approval.');
    }

    public function show($id)
    {
        $sermon = $this->sermonService->getSermonById($id);

        if (!$sermon) {
            return ApiResponse::error('Sermon not found.', 404);
        }

        return ApiResponse::success($sermon, 'Sermon details retrieved successfully.');
    }

    public function approve($id)
    {
        $sermon = $this->sermonService->approveSermon($id, auth()->id());
        return ApiResponse::success($sermon, 'Sermon approved and archived for public mosque use.');
    }

    public function reject($id)
    {
        $this->sermonService->rejectAndDestroySermon($id);
        return ApiResponse::success(null, 'Sermon has been rejected and deleted from the system.');
    }
    public function index()
    {
        $sermons = $this->sermonService->getAllSermons(auth()->user());
        return ApiResponse::success($sermons, 'All sermons retrieved successfully.');
    }
    public function pending() {
        $sermons = $this->sermonService->getPendingSermons();
        return ApiResponse::success($sermons, 'Pending sermons retrieved successfully.');
    }

    public function archived() {
        $sermons = $this->sermonService->getArchivedSermons();
        return ApiResponse::success($sermons, 'Archived sermons retrieved successfully.');
    }

    public function search(SearchSermonRequest $request)
    {
        $filters = $request->validated();
        $perPage = $filters['per_page'] ?? 15;

        $sermons = $this->sermonService->searchSermons($filters, auth()->user(), $perPage);

        return ApiResponse::success($sermons, 'Sermons filtered successfully.');
    }

}
