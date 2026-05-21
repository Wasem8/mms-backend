<?php

namespace Modules\Community\Repositories;

interface TameemRepositoryInterface
{
    public function getAll();
    public function getForMosqueManager($mosqueManagerId);
    public function findById($id);

    public function create(array $data, array $recipientIds);

    public function update(int $id,array $data);

    public function delete(int $id);

    public function syncRecipients(int $id , array $recipientIds);

    public function markAsRead($tameemId, $mosqueManagerId);
}
