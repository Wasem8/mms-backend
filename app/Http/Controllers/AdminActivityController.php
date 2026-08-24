<?php

namespace App\Http\Controllers;

use App\Services\SuperAdminActivityService;
use Illuminate\Http\Request;

class AdminActivityController extends Controller
{
    public function __construct(
        private readonly SuperAdminActivityService $service,
    ) {}

    /**
     * Super-admin activity feed (derived from existing tables — no new storage).
     * Sources: complaint status logs, sermon approvals, invitations, maintenance status logs.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['module', 'date_from', 'date_to', 'per_page', 'page']);

        $result = $this->service->getActivity($request->user(), $filters);

        return response()->json([
            'status'  => true,
            'message' => 'Success',
            'data'    => $result->items(),
            'meta'    => [
                'current_page' => $result->currentPage(),
                'last_page'    => $result->lastPage(),
                'per_page'     => $result->perPage(),
                'total'        => $result->total(),
                'from'         => $result->firstItem(),
                'to'           => $result->lastItem(),
            ],
            'links'   => [
                'first' => $result->url(1),
                'last'  => $result->url($result->lastPage()),
                'prev'  => $result->previousPageUrl(),
                'next'  => $result->nextPageUrl(),
            ],
        ]);
    }
}
