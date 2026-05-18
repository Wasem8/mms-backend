<?php

namespace Modules\Complaint\Repositories;

use Illuminate\Support\Collection;
use Modules\Complaint\Models\Complaint;

class ComplaintRepository implements ComplaintRepositoryInterface
{
    public function getFiltered(array $filters = []): Collection
    {
        $query = Complaint::with(['user', 'mosque', 'statusLogs', 'files']);

        if (isset($filters['mosque_id'])) {
            $query->where('mosque_id', $filters['mosque_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->get();
    }

    public function find(int $id): Complaint
    {
        return Complaint::with(['user', 'mosque', 'statusLogs', 'files'])->findOrFail($id);
    }

    public function findByComplaintNumber(string $complaintNumber): Complaint
    {
        return Complaint::with(['mosque', 'statusLogs'])
            ->where('complaint_number', $complaintNumber)
            ->firstOrFail();
    }

    public function create(array $data): Complaint
    {
        return Complaint::create($data);
    }

    public function update(int $id, array $data): Complaint
    {
        $complaint = $this->find($id);
        $complaint->update($data);

        return $complaint;
    }

    public function delete(int $id): bool
    {
        $complaint = $this->find($id);
        return $complaint->delete();
    }


    public function attachFiles(Complaint $complaint, array $files): void
    {
        $complaint->files()->createMany($files);
    }


    public function logStatusChange(Complaint $complaint, array $logData): void
    {
        $complaint->statusLogs()->create($logData);
    }
}
