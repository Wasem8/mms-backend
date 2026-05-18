<?php

namespace Modules\Community\Repositories;

interface SermonRepositoryInterface
{
    public function getAll(); // إضافة جديدة
    public function getAllPending();
    public function findById($id);
    public function create(array $data);
    public function attachAttachments($sermon, array $attachments);
    public function updateStatus($id, $status, $notes = null, $regionManagerId = null);
}
