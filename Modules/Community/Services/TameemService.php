<?php

namespace Modules\Community\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Modules\Community\Repositories\TameemRepositoryInterface;

class TameemService
{
protected $tameemRepo;

public function __construct(TameemRepositoryInterface $tameemRepo)
{
$this->tameemRepo = $tameemRepo;
}

public function getAllTameems()
{
return $this->tameemRepo->getAll();
}
    public function updateTameem(int $id, array $data, int $actorId)
    {
        $tameem = $this->tameemRepo->findById($id);

        if ($tameem->sender_id !== $actorId) {
            throw new AuthorizationException('غير مصرح لك بتعديل هذا التعميم.');
        }

        $payload = array_filter([
            'title'   => $data['title']   ?? null,
            'content' => $data['content'] ?? null,
        ], fn($v) => $v !== null);

        if (!empty($payload)) {
            $this->tameemRepo->update($id, $payload);
        }

        if (isset($data['recipient_ids'])) {
            $this->tameemRepo->syncRecipients($id, $data['recipient_ids']);
        }

        return $this->tameemRepo->findById($id);
    }

    
    public function deleteTameem(int $id, int $actorId): void
    {
        $tameem = $this->tameemRepo->findById($id);

        if ($tameem->sender_id !== $actorId) {
            throw new AuthorizationException('غير مصرح لك بحذف هذا التعميم.');
        }

        $this->tameemRepo->delete($id);
    }


    public function getMosqueManagerTameems($mosqueManagerId)
{
return $this->tameemRepo->getForMosqueManager($mosqueManagerId);
}

public function sendTameem(array $data, $senderId, array $recipientIds)
{
return DB::transaction(function () use ($data, $senderId, $recipientIds) {
$data['sender_id'] = $senderId;
$data['sent_at'] = Carbon::now();

return $this->tameemRepo->create($data, $recipientIds);
});
}

public function markTameemAsRead($tameemId, $mosqueManagerId)
{
return $this->tameemRepo->markAsRead($tameemId, $mosqueManagerId);
}
}
