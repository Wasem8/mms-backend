<?php

namespace Modules\Community\Repositories;

interface TameemRepositoryInterface
{
    public function getAll();
    public function getForMosqueManager($mosqueManagerId);
    public function findById($id);
    public function create(array $data, array $recipientIds);
    public function markAsRead($tameemId, $mosqueManagerId);
}
