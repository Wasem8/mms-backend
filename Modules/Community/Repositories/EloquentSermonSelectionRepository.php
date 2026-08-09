<?php

namespace Modules\Community\Repositories;

use Modules\Community\Models\SermonSelection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Modules\Community\Models\Sermon;

class EloquentSermonSelectionRepository implements SermonSelectionRepositoryInterface
{
    public function upsert(int $mosqueManagerId, int $sermonId, string $fridayDate): SermonSelection
    {
        return SermonSelection::updateOrCreate(
            ['mosque_manager_id' => $mosqueManagerId, 'friday_date' => $fridayDate],
            ['sermon_id' => $sermonId]
        );
    }

    public function findById(int $id): ?SermonSelection
    {
        return SermonSelection::with(['sermon.mosqueManager', 'mosqueManager'])->find($id);
    }

    public function search(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return SermonSelection::with(['sermon.mosqueManager', 'mosqueManager'])
            ->filter($filters)
            ->latest('friday_date')
            ->paginate($perPage);
    }

    public function upcomingPerMosque(): Collection
    {
        $today = Carbon::today()->toDateString();

        return SermonSelection::with(['sermon.mosqueManager', 'mosqueManager'])
            ->where('friday_date', '>=', $today)
            ->orderBy('friday_date')
            ->get()
            ->unique('mosque_manager_id')
            ->values();
    }

    public function delete(SermonSelection $selection): void
    {
        $selection->delete();
    }

   
}
