<?php

namespace Modules\Donation\Repositories;

use Modules\Donation\Models\Donation;

class DonationRepository implements DonationRepositoryInterface
{
    public function all()
    {
        return Donation::all();
    }

    public function create(array $data): Donation
    {
        return Donation::create($data);
    }

    public function find(int $id): ?Donation
    {
        return Donation::find($id);
    }

    public function update(int $id, array $data): Donation
    {
        $donation = Donation::findOrFail($id);
        $donation->update($data);

        return $donation;
    }

    public function delete(int $id): void
    {
        $donation = Donation::findOrFail($id);
        $donation->delete();
    }
    
    public function markCompleted(int $id): void
    {
        Donation::where('id', $id)->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);
    }
}
