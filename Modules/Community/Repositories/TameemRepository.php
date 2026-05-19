<?php

namespace Modules\Community\Repositories;


use Modules\Community\Models\Tameem;
use Modules\Community\Repositories\TameemRepositoryInterface;

class TameemRepository implements TameemRepositoryInterface
{
    public function getAll()
    {
        return Tameem::with([
            'sender:id,name',
            'recipients' => fn($q) => $q->select('users.id', 'users.name'),
        ])->latest('sent_at')->get();
    }
    public function getForMosqueManager($mosqueManagerId)
    {
        return Tameem::whereHas(
            'recipients',
            fn($q) =>
            $q->where('tameem_recipients.mosque_manager_id', $mosqueManagerId)
        )->with([
            'sender:id,name',
            'recipients' => fn($q) => $q
                ->select('users.id', 'users.name')
                ->where('tameem_recipients.mosque_manager_id', $mosqueManagerId),
        ])->latest('sent_at')->get();
    }

    public function findById($id)
    {
        return Tameem::with([
            'sender:id,name',
            'recipients' => fn($q) => $q->select('users.id', 'users.name'),
        ])->findOrFail($id);
    }

    public function create(array $data, array $recipientIds)
    {
        $tameem = Tameem::create($data);
        $tameem->recipients()->attach($recipientIds, ['is_read' => false]);

        return $this->findById($tameem->id);
    }

    public function update(int $id, array $data): bool
    {
        return Tameem::findOrFail($id)->update($data);
    }

    public function delete(int $id): bool
    {
        return Tameem::findOrFail($id)->delete();
    }

    public function syncRecipients(int $tameemId, array $recipientIds): void
    {
        Tameem::findOrFail($tameemId)->recipients()->sync($recipientIds);
    }

    public function markAsRead($tameemId, $mosqueManagerId)
    {
        $tameem = $this->findById($tameemId);
        $tameem->recipients()->updateExistingPivot($mosqueManagerId, [
            'is_read' => true,
            'read_at' => now(),
        ]);

        return true;
    }
}
