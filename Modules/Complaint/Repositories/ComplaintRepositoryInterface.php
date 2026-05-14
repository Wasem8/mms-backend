<?php

namespace Modules\Complaint\Repositories;

use Modules\Complaint\Models\Complaint;
use Illuminate\Support\Collection;

interface ComplaintRepositoryInterface
{
    public function getFiltered(array $filters = []): Collection;
    public function find(int $id): Complaint;
    public function findByComplaintNumber(string $complaintNumber): Complaint;
    public function create(array $data): Complaint;
    public function update(int $id, array $data): Complaint;
    public function delete(int $id): bool;

    // Specific to WASL requirements
    public function attachFiles(Complaint $complaint, array $files): void;
    public function logStatusChange(Complaint $complaint, array $logData): void;
}
