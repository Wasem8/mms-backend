<?php

namespace Modules\Volunteer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Modules\User\Models\User;
use Modules\User\Transformers\UserResource;
use Modules\Volunteer\Enums\ApplicationStatus;
use Modules\Volunteer\Models\VolunteerApplication;
use Modules\Volunteer\Models\VolunteerOpportunity;

class VolunteerController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = $request->user();

        $query = User::with(['roles.permissions'])
            ->whereHas('roles', fn($q) => $q->where('name', 'volunteer'))
            ->latest();

        // 🎯 super_admin sees all volunteers; others are scoped to their mosque
        if (! $currentUser->hasRole('super_admin') && $currentUser->mosque_id) {
            $query->where('mosque_id', $currentUser->mosque_id);
        }

        // 🔍 search by name / first_name / last_name / email / phone
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // 🔍 optional status filter (active / inactive)
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage    = $request->integer('per_page', 15);
        $volunteers  = $query->paginate($perPage);

        return ApiResponse::success(
            UserResource::collection($volunteers)->resolve($request),
            __('messages.volunteers_retrieved'),
            $volunteers
        );
    }

    /**
     * Mosque-manager dashboard cards, scoped to the manager's mosque:
     * - total opportunities
     * - pending (قائمة) applications
     * - volunteers in the mosque
     */
    public function stats(Request $request)
    {
        $user = $request->user();
        $mosqueId = $user->managedMosque?->id ?? $user->mosque_id;

        $opportunitiesTotal = $mosqueId
            ? VolunteerOpportunity::where('mosque_id', $mosqueId)->count()
            : 0;

        $pendingApplications = $mosqueId
            ? VolunteerApplication::where('status', ApplicationStatus::Pending)
                ->whereHas('opportunity', fn($q) => $q->where('mosque_id', $mosqueId))
                ->count()
            : 0;

        $volunteersCount = $mosqueId
            ? User::where('mosque_id', $mosqueId)
                ->whereHas('roles', fn($q) => $q->where('name', 'volunteer'))
                ->count()
            : 0;

        return ApiResponse::success([
            'opportunities_total'   => $opportunitiesTotal,
            'pending_applications'  => $pendingApplications,
            'volunteers_count'      => $volunteersCount,
        ], __('messages.volunteer_stats_retrieved'));
    }
}
