<?php

namespace Modules\Complaint\Repositories;


use Modules\Complaint\Models\Complaint;

class ComplaintRepository implements ComplaintRepositoryInterface
{
    public function all()
    {
        return Complaint::with(['user', 'mosque', 'statusLogs'])->get();
    }

    public function find($id)
    {
        return Complaint::with(['user', 'mosque', 'statusLogs'])->findOrFail($id);
    }

    public function create(array $data)
    {
        return Complaint::create($data);
    }

    public function update($id, array $data)
    {
        $complaint = $this->find($id);
        $complaint->update($data);
        return $complaint;
    }

    public function delete($id)
    {
        $complaint = $this->find($id);
        $complaint->delete();
        return $complaint;
    }
}
