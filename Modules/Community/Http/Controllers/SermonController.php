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

        return ApiResponse::success($sermon, __('messages.community.sermon_submitted'));
    }

    public function show($id)
    {
        $sermon = $this->sermonService->getSermonById($id);

        if (!$sermon) {
            return ApiResponse::error(__('messages.community.sermon_not_found'), 404);
        }

        return ApiResponse::success($sermon, __('messages.community.sermon_retrieved'));
    }

    public function approve($id)
    {
        $sermon = $this->sermonService->approveSermon($id, auth()->id());
        return ApiResponse::success($sermon, __('messages.community.sermon_approved'));
    }

    public function reject($id)
    {
        $this->sermonService->rejectAndDestroySermon($id);
        return ApiResponse::success(null, __('messages.community.sermon_rejected'));
    }
    public function index()
    {
        $sermons = $this->sermonService->getAllSermons(auth()->user());
        return ApiResponse::success($sermons, __('messages.community.sermons_retrieved'));
    }
    public function pending() {
        $sermons = $this->sermonService->getPendingSermons();
        return ApiResponse::success($sermons, __('messages.community.pending_sermons_retrieved'));
    }

    public function archived() {
        $sermons = $this->sermonService->getArchivedSermons();
        return ApiResponse::success($sermons, __('messages.community.archived_sermons_retrieved'));
    }

    public function search(SearchSermonRequest $request)
    {
        $filters = $request->validated();
        $perPage = $filters['per_page'] ?? 15;

        $sermons = $this->sermonService->searchSermons($filters, auth()->user(), $perPage);

        return ApiResponse::success($sermons, __('messages.community.sermons_filtered'));
    }

    public function mostSelected(Request $request)
    {
        $filters = $request->validate([
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
            'friday_date_from' => ['nullable', 'date'],
            'friday_date_to' => ['nullable', 'date'],
        ]);

        $sermons = $this->sermonService->getMostSelectedSermons(
            $filters['limit'] ?? 10,
            $filters['friday_date_from'] ?? null,
            $filters['friday_date_to'] ?? null,
        );

        return ApiResponse::success($sermons, __('messages.community.most_selected_sermons_retrieved'));
    }
}
