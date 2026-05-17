<?php

namespace Modules\Community\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
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
