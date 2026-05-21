<?php

namespace Modules\Community\Repositories;

use Modules\Community\Models\Sermon;
use Modules\Community\Repositories\SermonRepositoryInterface;


class SermonRepository implements SermonRepositoryInterface
{
    public function getAll()
    {
        return Sermon::with(['mosqueManager', 'regionManager', 'attachments'])->latest()->get();
    }

    public function getAllPending()
    {
        return Sermon::with(['mosqueManager', 'attachments'])
            ->where('status', 'Pending')
            ->latest()
            ->get();
    }

    public function findById($id)
    {
        return Sermon::findOrFail($id);
    }

    public function create(array $data)
    {
        return Sermon::create($data);
    }

    public function attachAttachments($sermon, array $attachments)
    {
        return $sermon->attachments()->createMany($attachments);
    }

    public function updateStatus($id, $status, $notes = null, $regionManagerId = null)
    {
        $sermon = $this->findById($id);
        $sermon->update([
            'status' => $status,
            'notes' => $notes,
            'region_manager_id' => $regionManagerId
        ]);
        return $sermon;
    }
}
