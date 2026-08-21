<?php

namespace Modules\Community\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Modules\Community\Repositories\TameemRepositoryInterface;
use Modules\Community\Events\TameemSent;
use Modules\Community\Events\TameemUpdated;

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
            throw new AuthorizationException(__('messages.community.cannot_edit_tameem'));
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

        $updated = $this->tameemRepo->findById($id);

        $recipientIds = $data['recipient_ids'] ?? $updated->recipients()->pluck('users.id')->toArray();

        event(new TameemUpdated($updated, $recipientIds));

        return $updated;
    }


    public function deleteTameem(int $id, int $actorId): void
    {
        $tameem = $this->tameemRepo->findById($id);

        if ($tameem->sender_id !== $actorId) {
            throw new AuthorizationException(__('messages.community.cannot_delete_tameem'));
        }

        $this->tameemRepo->delete($id);
    }


    public function getMosqueManagerTameems($mosqueManagerId)
    {
        return $this->tameemRepo->getForMosqueManager($mosqueManagerId);
    }

    public function getSentTameems($senderId)
    {
        return $this->tameemRepo->getSentByManager($senderId);
    }

    public function getTameemById($id)
    {
        return $this->tameemRepo->findById($id);
    }
    public function sendTameem(array $data, $senderId, array $recipientIds)
    {
        return DB::transaction(function () use ($data, $senderId, $recipientIds) {
            $data['sender_id'] = $senderId;
            $data['sent_at'] = Carbon::now();

            $tameem = $this->tameemRepo->create($data, $recipientIds);

            event(new TameemSent($tameem, $recipientIds));

            return $tameem;
        });
    }

    public function markTameemAsRead($tameemId, $mosqueManagerId)
    {
        return $this->tameemRepo->markAsRead($tameemId, $mosqueManagerId);
    }
}
