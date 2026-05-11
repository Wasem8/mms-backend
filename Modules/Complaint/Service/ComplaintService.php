<?php

namespace Modules\Complaint\Service;

use Modules\Complaint\Repositories\ComplaintRepositoryInterface;

class ComplaintService
{
    protected $complaintRepository;

    public function __construct(ComplaintRepositoryInterface $complaintRepository)
    {
        $this->complaintRepository = $complaintRepository;
    }

    public function all()
    {
        return $this->complaintRepository->all();
    }

    public function find($id)
    {
        return $this->complaintRepository->find($id);
    }

    public function create(array $data)
    {
        return $this->complaintRepository->create($data);
    }

    public function update($id, array $data)
    {
        return $this->complaintRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->complaintRepository->delete($id);
    }
}
