<?php

namespace Modules\Complaint\Service;

use Modules\Complaint\Repositories\ComplaintStatusLogRepository;

class ComplaintService
{
    protected $statusLogRepository;

    public function __construct(ComplaintStatusLogRepository $statusLogRepository)
    {
        $this->statusLogRepository = $statusLogRepository;
    }

    public function getAllStatusLogs()
    {
        return $this->statusLogRepository->all();
    }

    public function getStatusLog($id)
    {
        return $this->statusLogRepository->find($id);
    }

    public function createStatusLog(array $data)
    {
        return $this->statusLogRepository->create($data);
    }

    public function deleteStatusLog($id)
    {
        return $this->statusLogRepository->delete($id);
    }
}
