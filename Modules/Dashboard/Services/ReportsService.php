<?php

namespace Modules\Dashboard\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Complaint\Models\Complaint;
use Modules\Donation\Models\Donation;
use Modules\MaintenanceRequest\Models\Maintenance;
use Modules\User\Models\User;

class ReportsService
{
    /**
     * تقارير مُفلترة حسب تاريخ (من/إلى) ومسجد اختياري.
     * - التبرعات: للمديرين (المنطقة + المسجد). مدير المنطقة يمكنه اختيار أي مسجد.
     * - الصيانة: للمديرين. للمنطقة فقط بشكل افتراضي ويمكن تصفيتها لمسجد.
     * - الشكاوى: للمديرين. مدير المسجد مُقيّد بمسجده.
     */

    private function applyDate($query, array $filters)
    {
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query;
    }

    private function managerMosqueId(User $user): ?int
    {
        if ($user->mosque_id) {
            return $user->mosque_id;
        }

        $mosque = \Modules\Mosque\Models\Mosque::where('manager_id', $user->id)->first();

        return $mosque?->id;
    }

    private function perPage(array $filters): int
    {
        return isset($filters['per_page'])
            ? max(1, min(100, (int) $filters['per_page']))
            : 15;
    }

    private function paginateOrAll($query, array $filters, bool $all): LengthAwarePaginator|Collection
    {
        if ($all) {
            return $query->latest()->get();
        }

        return $query->latest()->paginate($this->perPage($filters));
    }

    // ===== التبرعات =====

    private function donationsQuery(User $user, array $filters)
    {
        $query = Donation::with(['mosque:id,name', 'campaign:id,title', 'user:id,name'])
            ->whereIn('status', ['paid', 'completed', 'approved']);

        if ($user->hasRole('super_admin')) {
            if (! empty($filters['mosque_id'])) {
                $query->where('mosque_id', $filters['mosque_id']);
            }
        } elseif ($user->hasRole('mosque_manager')) {
            $mosqueId = $this->managerMosqueId($user);
            if ($mosqueId) {
                $query->where('mosque_id', $mosqueId);
            }
        }

        return $this->applyDate($query, $filters);
    }

    public function donationsReport(User $user, array $filters = []): array
    {
        $query = $this->donationsQuery($user, $filters);

        $summary = [
            'count'             => (clone $query)->count(),
            'total_base_amount' => (float) (clone $query)->sum('base_amount'),
            'total_amount'      => (float) (clone $query)->sum('amount'),
            'currency'          => 'SYP',
        ];

        $items = $this->paginateOrAll($query, $filters, false);

        return ['summary' => $summary, 'items' => $items];
    }

    public function donationsExport(User $user, array $filters = []): array
    {
        $query = $this->donationsQuery($user, $filters);

        $summary = [
            'count'             => (clone $query)->count(),
            'total_base_amount' => (float) (clone $query)->sum('base_amount'),
            'total_amount'      => (float) (clone $query)->sum('amount'),
            'currency'          => 'SYP',
        ];

        $items = $this->paginateOrAll($query, $filters, true);

        return ['summary' => $summary, 'items' => $items];
    }

    // ===== الصيانة =====

    private function maintenanceQuery(User $user, array $filters)
    {
        $query = Maintenance::with(['mosque:id,name']);

        if ($user->hasRole('super_admin')) {
            if (! empty($filters['mosque_id'])) {
                $query->where('mosque_id', $filters['mosque_id']);
            }
        } elseif ($user->hasRole('mosque_manager')) {
            $mosqueId = $this->managerMosqueId($user);
            if ($mosqueId) {
                $query->where('mosque_id', $mosqueId);
            }
        }

        return $this->applyDate($query, $filters);
    }

    public function maintenanceReport(User $user, array $filters = []): array
    {
        $query = $this->maintenanceQuery($user, $filters);

        $summary = [
            'count'       => (clone $query)->count(),
            'by_status'   => (clone $query)->selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status'),
            'by_priority' => (clone $query)->selectRaw('priority, count(*) as count')->groupBy('priority')->pluck('count', 'priority'),
        ];

        $items = $this->paginateOrAll($query, $filters, false);

        return ['summary' => $summary, 'items' => $items];
    }

    public function maintenanceExport(User $user, array $filters = []): array
    {
        $query = $this->maintenanceQuery($user, $filters);

        $summary = [
            'count'       => (clone $query)->count(),
            'by_status'   => (clone $query)->selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status'),
            'by_priority' => (clone $query)->selectRaw('priority, count(*) as count')->groupBy('priority')->pluck('count', 'priority'),
        ];

        $items = $this->paginateOrAll($query, $filters, true);

        return ['summary' => $summary, 'items' => $items];
    }

    // ===== الشكاوى =====

    private function complaintsQuery(User $user, array $filters)
    {
        $query = Complaint::with(['mosque:id,name', 'assignedAdmin:id,name']);

        if ($user->hasRole('super_admin')) {
            if (! empty($filters['mosque_id'])) {
                $query->where('mosque_id', $filters['mosque_id']);
            }
        } elseif ($user->hasRole('mosque_manager')) {
            $mosqueId = $this->managerMosqueId($user);
            if ($mosqueId) {
                $query->where('mosque_id', $mosqueId);
            }
        }

        return $this->applyDate($query, $filters);
    }

    public function complaintsReport(User $user, array $filters = []): array
    {
        $query = $this->complaintsQuery($user, $filters);

        $summary = [
            'count'     => (clone $query)->count(),
            'by_status' => (clone $query)->selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status'),
            'urgent'    => (clone $query)->where('priority', 'high')->count(),
        ];

        $items = $this->paginateOrAll($query, $filters, false);

        return ['summary' => $summary, 'items' => $items];
    }

    public function complaintsExport(User $user, array $filters = []): array
    {
        $query = $this->complaintsQuery($user, $filters);

        $summary = [
            'count'     => (clone $query)->count(),
            'by_status' => (clone $query)->selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status'),
            'urgent'    => (clone $query)->where('priority', 'high')->count(),
        ];

        $items = $this->paginateOrAll($query, $filters, true);

        return ['summary' => $summary, 'items' => $items];
    }
}
