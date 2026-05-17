<?php

namespace Modules\Community\Repositories;


use Modules\Community\Models\Tameem;
use Modules\Community\Repositories\TameemRepositoryInterface;

class TameemRepository implements TameemRepositoryInterface
{
public function getAll()
{
return Tameem::with('recipients')->latest('sent_at')->get();
}

public function getForMosqueManager($mosqueManagerId)
{
return Tameem::whereHas('recipients', function ($query) use ($mosqueManagerId) {
$query->where('mosque_manager_id', $mosqueManagerId);
})->with(['sender', 'recipients' => function ($query) use ($mosqueManagerId) {
$query->where('mosque_manager_id', $mosqueManagerId);
}])->latest('sent_at')->get();
}

public function findById($id)
{
return Tameem::findOrFail($id);
}

public function create(array $data, array $recipientIds)
{
$tameem = Tameem::create($data);
$tameem->recipients()->attach($recipientIds, ['is_read' => false]);

return $tameem;
}

public function markAsRead($tameemId, $mosqueManagerId)
{
$tameem = $this->findById($tameemId);
$tameem->recipients()->updateExistingPivot($mosqueManagerId, ['is_read' => true]);

return true;
}
}
