<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Complaint\Models\ComplaintStatusLog;
use Modules\Community\Models\Sermon;
use Modules\Invitation\Models\Invitation;
use Modules\MaintenanceRequest\Models\MaintenanceStatusLog;
use Modules\User\Models\User;

/**
 * Aggregates super-admin activity by reading existing tables that record the actor.
 * No new storage is created — the feed is derived from complaint/sermon/invitation logs,
 * while maintenance entries show ALL requests with the mosque manager (manager_id) as the actor.
 */
class SuperAdminActivityService
{
    public function getActivity(User $actor, array $filters = []): LengthAwarePaginator
    {
        $rows = collect();

        // 1) Complaint status changes (changed_by = user id)
        $complaintLogs = ComplaintStatusLog::with(['complaint', 'user'])
            ->where('changed_by', $actor->id)
            ->when($filters['date_from'] ?? null, fn($q, $v) => $q->whereDate('changed_at', '>=', $v))
            ->when($filters['date_to']   ?? null, fn($q, $v) => $q->whereDate('changed_at', '<=', $v))
            ->get();

        foreach ($complaintLogs as $log) {
            $rows->push([
                'module'      => 'complaints',
                'action_key'  => 'complaint_status_changed',
                'description' => 'تغيير حالة الشكوى رقم #' . ($log->complaint_id ?? '?') .
                    ' من ' . ($log->old_status ?? '—') . ' إلى ' . ($log->new_status ?? '—'),
                'target_type' => 'complaint',
                'target_id'   => $log->complaint_id,
                'actor_id'    => $log->changed_by,
                'actor_name'  => $log->user?->name,
                'created_at'  => $log->changed_at,
            ]);
        }

        // 2) Sermon approvals (region_manager_id = user id)
        $sermons = Sermon::with('regionManager')
            ->where('region_manager_id', $actor->id)
            ->when($filters['date_from'] ?? null, fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['date_to']   ?? null, fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->get();

        foreach ($sermons as $sermon) {
            $rows->push([
                'module'      => 'sermons',
                'action_key'  => 'sermon_approved',
                'description' => 'اعتماد خطبة: ' . ($sermon->title ?? '—'),
                'target_type' => 'sermon',
                'target_id'   => $sermon->id,
                'actor_id'    => $sermon->region_manager_id,
                'actor_name'  => $sermon->regionManager?->name,
                'created_at'  => $sermon->created_at,
            ]);
        }

        // 3) Invitations created (created_by = user id)
        $invitations = Invitation::with('creator')
            ->where('created_by', $actor->id)
            ->when($filters['date_from'] ?? null, fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['date_to']   ?? null, fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->get();

        foreach ($invitations as $inv) {
            $rows->push([
                'module'      => 'invitations',
                'action_key'  => 'invitation_created',
                'description' => 'إنشاء دعوة لـ ' . ($inv->email ?? '—') . ' (الدور: ' . ($inv->role ?? '—') . ')',
                'target_type' => 'invitation',
                'target_id'   => $inv->id,
                'actor_id'    => $inv->created_by,
                'actor_name'  => $inv->creator?->name,
                'created_at'  => $inv->created_at,
            ]);
        }

        // 4) Maintenance status changes — actor is the mosque manager (manager_id of the request's mosque)
        $maintenanceLogs = MaintenanceStatusLog::with('maintenance.mosque.manager')
            ->when($filters['date_from'] ?? null, fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['date_to']   ?? null, fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->get();

        foreach ($maintenanceLogs as $log) {
            $manager = $log->maintenance?->mosque?->manager;

            $rows->push([
                'module'      => 'maintenance',
                'action_key'  => 'maintenance_status_changed',
                'description' => 'تغيير حالة طلب الصيانة رقم #' . ($log->maintenance_id ?? '?') .
                    ' من ' . ($log->old_status ?? '—') . ' إلى ' . ($log->new_status ?? '—'),
                'target_type' => 'maintenance',
                'target_id'   => $log->maintenance_id,
                'actor_id'    => $manager?->id,
                'actor_name'  => $manager?->name,
                'created_at'  => $log->created_at,
            ]);
        }

        if (!empty($filters['module'])) {
            $rows = $rows->where('module', $filters['module']);
        }

        $rows = $rows->sortByDesc(fn($r) => $r['created_at'])->values();

        $perPage = isset($filters['per_page']) ? max(1, min(100, (int) $filters['per_page'])) : 15;
        $page    = isset($filters['page']) ? max(1, (int) $filters['page']) : 1;
        $total   = $rows->count();
        $items   = $rows->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }
}
