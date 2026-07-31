<?php

namespace Modules\Community\Repositories;

use Modules\Community\Models\Sermon;
use Modules\Community\Repositories\SermonRepositoryInterface;
use Illuminate\Support\Collection;


class SermonRepository implements SermonRepositoryInterface
{
    public function create(array $data): Sermon
    {
        return Sermon::create($data);
    }

    public function findById(int $id): ?Sermon
    {
        return Sermon::findOrFail($id);
    }

    public function updateStatus(Sermon $sermon, string $status): bool
    {
        return $sermon->update(['status' => $status]);
    }

    public function delete(Sermon $sermon): bool
    {
        return $sermon->delete();
    }

    public function getExpiredPendingSermons(string $currentDate)
    {
        return Sermon::where('status', 'pending')
            ->where('sermon_date', '<=', $currentDate)
            ->get();
    }
    public function search(array $filters, int $perPage = 15)
    {
        return Sermon::with(['attachments', 'mosqueManager', 'regionManager'])
            ->filter($filters)
            ->when(
                $filters['sort'] ?? null,
                function ($q, $sort) {
                    // مثال: sort=sermon_date:asc أو sort=created_at:desc
                    [$column, $direction] = array_pad(explode(':', $sort), 2, 'desc');
                    $allowedColumns = ['sermon_date', 'created_at', 'title', 'status'];
                    if (in_array($column, $allowedColumns, true)) {
                        $q->orderBy($column, $direction === 'asc' ? 'asc' : 'desc');
                    }
                },
                fn($q) => $q->latest()
            )
            ->paginate($perPage);
    }

    public function mostSelected(int $limit = 10, ?string $fridayDateFrom = null, ?string $fridayDateTo = null): \Illuminate\Support\Collection
    {
        $dateFilter = function ($q) use ($fridayDateFrom, $fridayDateTo) {
            $q->when($fridayDateFrom, fn($qq, $date) => $qq->whereDate('friday_date', '>=', $date))
                ->when($fridayDateTo, fn($qq, $date) => $qq->whereDate('friday_date', '<=', $date));
        };

        return Sermon::query()
            ->withCount(['selections' => $dateFilter])
            ->whereHas('selections', $dateFilter)
            ->orderByDesc('selections_count')
            ->with(['mosqueManager:id,name,mosque_id']) // ← فقط الأعمدة الآمنة
            ->limit($limit)
            ->get();
    }
}
