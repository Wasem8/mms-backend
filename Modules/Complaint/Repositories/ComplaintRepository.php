<?php

namespace Modules\Complaint\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Complaint\Models\Complaint;

class ComplaintRepository implements ComplaintRepositoryInterface
{
    public function getFiltered(array $filters = []): LengthAwarePaginator
    {
        $query = Complaint::with(['user', 'mosque', 'statusLogs', 'files', 'assignedAdmin']);

        if (array_key_exists('mosque_id', $filters)) {
            $query->where('mosque_id', $filters['mosque_id']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['status']))     $query->where('status', $filters['status']);
        if (isset($filters['complaint_type'])) $query->where('complaint_type', $filters['complaint_type']);
        if (isset($filters['priority']))   $query->where('priority', $filters['priority']);
        if (isset($filters['search'])) {
            $s = $filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('complaint_number', 'like', "%{$s}%")
                  ->orWhere('title', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            });
        }
        $perPage = isset($filters['per_page']) ? max(1, min(100, (int) $filters['per_page'])) : 15;

        return $query->latest()->paginate($perPage);

    }

    public function find(int $id): Complaint
    {
        return Complaint::with(['user', 'mosque', 'statusLogs', 'files', 'assignedAdmin'])->findOrFail($id);
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

    public function assignToAdmin(int $complaintId, int $adminId): Complaint
{
    $complaint = Complaint::findOrFail($complaintId);

    $complaint->update([
        'assigned_admin_id' => $adminId,
    ]);

    return $complaint->fresh(['assignedAdmin']);
}

}
