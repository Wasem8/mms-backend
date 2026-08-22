<?php

namespace Modules\Dashboard\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Complaint\Models\Complaint;
use Modules\Donation\Models\Donation;
use Modules\MaintenanceRequest\Models\Maintenance;
use Modules\Mosque\Models\Mosque;
use Modules\User\Models\User;

class ReportsService
{
    /*
    |--------------------------------------------------------------------------
    | Date Filters
    |--------------------------------------------------------------------------
    */

    private function applyDate(
        $query,
        array $filters
    ) {
        if (!empty($filters['date_from'])) {
            $query->whereDate(
                'created_at',
                '>=',
                $filters['date_from']
            );
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate(
                'created_at',
                '<=',
                $filters['date_to']
            );
        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | Mosque of Manager
    |--------------------------------------------------------------------------
    */

    private function managerMosqueId(User $user): ?int
    {
        if ($user->mosque_id) {
            return $user->mosque_id;
        }

        $mosque = Mosque::where(
            'manager_id',
            $user->id
        )->first();

        return $mosque?->id;
    }

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    private function perPage(array $filters): int
    {
        if (!isset($filters['per_page'])) {
            return 15;
        }

        return max(
            1,
            min(
                100,
                (int) $filters['per_page']
            )
        );
    }

    private function paginateOrAll(
        $query,
        array $filters,
        bool $all
    ): LengthAwarePaginator|Collection {
        $query->latest();

        if ($all) {
            return $query->get();
        }

        return $query->paginate(
            $this->perPage($filters)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Donations Query
    |--------------------------------------------------------------------------
    */

    private function donationsQuery(
        User $user,
        array $filters
    ) {
        $query = Donation::with([
            'mosque:id,name',
            'campaign:id,title',
            'user:id,name',
        ])->whereIn(
            'status',
            [
                'paid',
                'completed',
                'approved',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */

        if ($user->hasRole('super_admin')) {

            if (!empty($filters['mosque_id'])) {
                $query->where(
                    'mosque_id',
                    $filters['mosque_id']
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Mosque Manager
        |--------------------------------------------------------------------------
        */

        elseif ($user->hasRole('mosque_manager')) {

            $mosqueId = $this->managerMosqueId($user);

            if ($mosqueId) {
                $query->where(
                    'mosque_id',
                    $mosqueId
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Region Manager
        |--------------------------------------------------------------------------
        */

        elseif ($user->hasRole('region_manager')) {

            /*
             * مدير المنطقة يرى جميع المساجد.
             * ويمكنه اختيار مسجد محدد باستخدام mosque_id.
             */

            if (!empty($filters['mosque_id'])) {
                $query->where(
                    'mosque_id',
                    $filters['mosque_id']
                );
            }
        }

        return $this->applyDate(
            $query,
            $filters
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Donations Report
    |--------------------------------------------------------------------------
    */

    public function donationsReport(
        User $user,
        array $filters = []
    ): array {
        $query = $this->donationsQuery(
            $user,
            $filters
        );

        $summary = [
            'count' =>
                (clone $query)->count(),

            'total_base_amount' =>
                (float) (clone $query)->sum(
                    'base_amount'
                ),

            'total_amount' =>
                (float) (clone $query)->sum(
                    'amount'
                ),

            'currency' =>
                'SYP',
        ];

        $items = $this->paginateOrAll(
            $query,
            $filters,
            false
        );

        return [
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Donations Export
    |--------------------------------------------------------------------------
    */

    public function donationsExport(
        User $user,
        array $filters = []
    ): array {
        $query = $this->donationsQuery(
            $user,
            $filters
        );

        $summary = [
            'count' =>
                (clone $query)->count(),

            'total_base_amount' =>
                (float) (clone $query)->sum(
                    'base_amount'
                ),

            'total_amount' =>
                (float) (clone $query)->sum(
                    'amount'
                ),

            'currency' =>
                'SYP',
        ];

        $items = $this->paginateOrAll(
            $query,
            $filters,
            true
        );

        return [
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Maintenance Query
    |--------------------------------------------------------------------------
    */

    private function maintenanceQuery(
        User $user,
        array $filters
    ) {
        $query = Maintenance::with([
            'mosque:id,name',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */

        if ($user->hasRole('super_admin')) {

            if (!empty($filters['mosque_id'])) {
                $query->where(
                    'mosque_id',
                    $filters['mosque_id']
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Mosque Manager
        |--------------------------------------------------------------------------
        */

        elseif ($user->hasRole('mosque_manager')) {

            $mosqueId = $this->managerMosqueId($user);

            if ($mosqueId) {
                $query->where(
                    'mosque_id',
                    $mosqueId
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Region Manager
        |--------------------------------------------------------------------------
        */

        elseif ($user->hasRole('region_manager')) {

            if (!empty($filters['mosque_id'])) {
                $query->where(
                    'mosque_id',
                    $filters['mosque_id']
                );
            }
        }

        return $this->applyDate(
            $query,
            $filters
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Maintenance Summary
    |--------------------------------------------------------------------------
    */

    private function buildMaintenanceSummary(
        $query
    ): array {
        /*
        |--------------------------------------------------------------------------
        | Main Counters
        |--------------------------------------------------------------------------
        */

        $total = (clone $query)->count();

        $pending = (clone $query)
            ->where('status', 'pending')
            ->count();

        $inProgress = (clone $query)
            ->where('status', 'in_progress')
            ->count();

        $completed = (clone $query)
            ->where('status', 'completed')
            ->count();

        $cancelled = (clone $query)
            ->where('status', 'cancelled')
            ->count();

        $highPriority = (clone $query)
            ->whereIn(
                'priority',
                [
                    'high',
                    'urgent',
                ]
            )
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Completion Percentage
        |--------------------------------------------------------------------------
        */

        $completionPercentage = $total > 0
            ? round(
                ($completed / $total) * 100,
                1
            )
            : 0;

        /*
        |--------------------------------------------------------------------------
        | By Status
        |--------------------------------------------------------------------------
        */

        $statusCounts = (clone $query)
            ->selectRaw(
                'status, COUNT(*) as count'
            )
            ->groupBy('status')
            ->pluck(
                'count',
                'status'
            );

        /*
        |--------------------------------------------------------------------------
        | By Priority
        |--------------------------------------------------------------------------
        */

        $priorityCounts = (clone $query)
            ->selectRaw(
                'priority, COUNT(*) as count'
            )
            ->groupBy('priority')
            ->pluck(
                'count',
                'priority'
            );

        return [
            'total' =>
                $total,

            'pending' =>
                $pending,

            'in_progress' =>
                $inProgress,

            'completed' =>
                $completed,

            'cancelled' =>
                $cancelled,

            'high_priority' =>
                $highPriority,

            'completion_percentage' =>
                $completionPercentage,

            'by_status' => [
                'pending' =>
                    (int) (
                        $statusCounts['pending']
                        ?? 0
                    ),

                'in_progress' =>
                    (int) (
                        $statusCounts['in_progress']
                        ?? 0
                    ),

                'completed' =>
                    (int) (
                        $statusCounts['completed']
                        ?? 0
                    ),

                'cancelled' =>
                    (int) (
                        $statusCounts['cancelled']
                        ?? 0
                    ),
            ],

            'by_priority' => [
                'low' =>
                    (int) (
                        $priorityCounts['low']
                        ?? 0
                    ),

                'medium' =>
                    (int) (
                        $priorityCounts['medium']
                        ?? 0
                    ),

                'high' =>
                    (int) (
                        $priorityCounts['high']
                        ?? 0
                    ),

                'urgent' =>
                    (int) (
                        $priorityCounts['urgent']
                        ?? 0
                    ),
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Maintenance Report
    |--------------------------------------------------------------------------
    */

    public function maintenanceReport(
        User $user,
        array $filters = []
    ): array {
        $query = $this->maintenanceQuery(
            $user,
            $filters
        );

        $summary = $this->buildMaintenanceSummary(
            $query
        );

        $items = $this->paginateOrAll(
            $query,
            $filters,
            false
        );

        return [
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Maintenance Export
    |--------------------------------------------------------------------------
    */

    public function maintenanceExport(
        User $user,
        array $filters = []
    ): array {
        $query = $this->maintenanceQuery(
            $user,
            $filters
        );

        $summary = $this->buildMaintenanceSummary(
            $query
        );

        $items = $this->paginateOrAll(
            $query,
            $filters,
            true
        );

        return [
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Complaints Query
    |--------------------------------------------------------------------------
    */

    private function complaintsQuery(
        User $user,
        array $filters
    ) {
        $query = Complaint::with([
            'mosque:id,name',
            'assignedAdmin:id,name',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */

        if ($user->hasRole('super_admin')) {

            if (!empty($filters['mosque_id'])) {
                $query->where(
                    'mosque_id',
                    $filters['mosque_id']
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Mosque Manager
        |--------------------------------------------------------------------------
        */

        elseif ($user->hasRole('mosque_manager')) {

            $mosqueId = $this->managerMosqueId($user);

            if ($mosqueId) {
                $query->where(
                    'mosque_id',
                    $mosqueId
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Region Manager
        |--------------------------------------------------------------------------
        */

        elseif ($user->hasRole('region_manager')) {

            if (!empty($filters['mosque_id'])) {
                $query->where(
                    'mosque_id',
                    $filters['mosque_id']
                );
            }
        }

        return $this->applyDate(
            $query,
            $filters
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Complaints Summary
    |--------------------------------------------------------------------------
    */

    private function buildComplaintsSummary(
        $query
    ): array {
        /*
        |--------------------------------------------------------------------------
        | Main Counters
        |--------------------------------------------------------------------------
        */

        $total = (clone $query)->count();

        $pending = (clone $query)
            ->where(
                'status',
                'pending'
            )
            ->count();

        $inProgress = (clone $query)
            ->where(
                'status',
                'in_progress'
            )
            ->count();

        $resolved = (clone $query)
            ->where(
                'status',
                'resolved'
            )
            ->count();

        $closed = (clone $query)
            ->where(
                'status',
                'closed'
            )
            ->count();

        $rejected = (clone $query)
            ->where(
                'status',
                'rejected'
            )
            ->count();

        /*
        |--------------------------------------------------------------------------
        | High / Urgent Priority
        |--------------------------------------------------------------------------
        */

        $highPriority = (clone $query)
            ->whereIn(
                'priority',
                [
                    'high',
                    'urgent',
                ]
            )
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Completion Percentage
        |--------------------------------------------------------------------------
        |
        | تعتبر الشكوى منجزة إذا كانت:
        | resolved أو closed
        |
        */

        $completedCount =
            $resolved + $closed;

        $completionPercentage = $total > 0
            ? round(
                ($completedCount / $total) * 100,
                1
            )
            : 0;

        /*
        |--------------------------------------------------------------------------
        | By Status
        |--------------------------------------------------------------------------
        */

        $statusCounts = (clone $query)
            ->selectRaw(
                'status, COUNT(*) as count'
            )
            ->groupBy('status')
            ->pluck(
                'count',
                'status'
            );

        /*
        |--------------------------------------------------------------------------
        | By Priority
        |--------------------------------------------------------------------------
        */

        $priorityCounts = (clone $query)
            ->selectRaw(
                'priority, COUNT(*) as count'
            )
            ->groupBy('priority')
            ->pluck(
                'count',
                'priority'
            );

        return [
            'total' =>
                $total,

            'pending' =>
                $pending,

            'in_progress' =>
                $inProgress,

            'resolved' =>
                $resolved,

            'closed' =>
                $closed,

            'rejected' =>
                $rejected,

            'high_priority' =>
                $highPriority,

            'completion_percentage' =>
                $completionPercentage,

            'by_status' => [
                'pending' =>
                    (int) (
                        $statusCounts['pending']
                        ?? 0
                    ),

                'in_progress' =>
                    (int) (
                        $statusCounts['in_progress']
                        ?? 0
                    ),

                'resolved' =>
                    (int) (
                        $statusCounts['resolved']
                        ?? 0
                    ),

                'closed' =>
                    (int) (
                        $statusCounts['closed']
                        ?? 0
                    ),

                'rejected' =>
                    (int) (
                        $statusCounts['rejected']
                        ?? 0
                    ),
            ],

            'by_priority' => [
                'low' =>
                    (int) (
                        $priorityCounts['low']
                        ?? 0
                    ),

                'medium' =>
                    (int) (
                        $priorityCounts['medium']
                        ?? 0
                    ),

                'high' =>
                    (int) (
                        $priorityCounts['high']
                        ?? 0
                    ),

                'urgent' =>
                    (int) (
                        $priorityCounts['urgent']
                        ?? 0
                    ),
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Complaints Report
    |--------------------------------------------------------------------------
    */

    public function complaintsReport(
        User $user,
        array $filters = []
    ): array {
        $query = $this->complaintsQuery(
            $user,
            $filters
        );

        $summary = $this->buildComplaintsSummary(
            $query
        );

        $items = $this->paginateOrAll(
            $query,
            $filters,
            false
        );

        return [
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Complaints Export
    |--------------------------------------------------------------------------
    */

    public function complaintsExport(
        User $user,
        array $filters = []
    ): array {
        $query = $this->complaintsQuery(
            $user,
            $filters
        );

        $summary = $this->buildComplaintsSummary(
            $query
        );

        $items = $this->paginateOrAll(
            $query,
            $filters,
            true
        );

        return [
            'summary' => $summary,
            'items' => $items,
        ];
    }
}
