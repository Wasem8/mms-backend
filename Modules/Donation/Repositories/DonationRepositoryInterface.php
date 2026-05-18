<?php

namespace Modules\Donation\Repositories;

use Modules\Donation\Models\Donation;

interface DonationRepositoryInterface
{
    public function all();

    public function create(array $data): Donation;

    public function find(int $id): ?Donation;

    public function update(int $id, array $data): Donation;

    public function delete(int $id): void;
    public function markCompleted(int $id): void;
}
