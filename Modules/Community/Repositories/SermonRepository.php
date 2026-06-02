<?php

namespace Modules\Community\Repositories;

use Modules\Community\Models\Sermon;
use Modules\Community\Repositories\SermonRepositoryInterface;


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
}
