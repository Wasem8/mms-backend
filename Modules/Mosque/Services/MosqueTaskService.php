<?php

namespace Modules\Mosque\Services;


use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\Mosque\DTOs\CreateMosqueTaskDTO;
use Modules\Mosque\DTOs\UpdateMosqueTaskDTO;
use Modules\Mosque\Models\MosqueTask;
use Modules\Mosque\Repositories\MosqueTaskRepositoryInterface;

class MosqueTaskService
{
    public function __construct(
        private readonly MosqueTaskRepositoryInterface $repository
    ) {}

    public function listForDate(int $mosqueId, string $date, ?string $status, ?string $category): array
    {
        $carbonDate = Carbon::parse($date);
        $tasks = $this->repository->findForDate($mosqueId, $carbonDate, $status, $category);

        $total = $tasks->count();
        $completed = $tasks->where('is_completed', true)->count();

        return [
            'tasks'       => $tasks,
            'total'       => $total,
            'completed'   => $completed,
            'percentage'  => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
            'by_category' => $tasks->groupBy(fn(MosqueTask $t) => $t->category->value)
                ->map->count(),
        ];
    }

    public function dateTabs(int $mosqueId): array
    {
        $today = now();

        $dates = [
            'today'      => $today->copy(),
            'tomorrow'   => $today->copy()->addDay(),
            'day_after'  => $today->copy()->addDays(2),
            'friday'     => $today->copy()->next('Friday'),
            'next_week'  => $today->copy()->addWeek()->startOfWeek(),
        ];

        return $this->repository->countsByDate($mosqueId, $dates);
    }

    public function create(CreateMosqueTaskDTO $dto): MosqueTask
    {
        return $this->repository->create($dto);
    }

    public function update(MosqueTask $task, UpdateMosqueTaskDTO $dto): MosqueTask
    {
        return $this->repository->update($task, $dto);
    }

    public function toggleComplete(MosqueTask $task): MosqueTask
    {
        return $this->repository->toggleComplete($task);
    }

    public function delete(MosqueTask $task): bool
    {
        return $this->repository->delete($task);
    }

    public function listForNextWeek(int $mosqueId, ?string $status = null, ?string $category = null): array
    {
        $from = now()->startOfDay();
        $to = now()->copy()->addDays(7)->endOfDay();

        $tasks = $this->repository->findForRange($mosqueId, $from, $to, $status, $category);

        return array_merge(
            $this->buildResult($tasks),
            [
                'from' => $from->toDateString(),
                'to'   => $to->toDateString(),
            ]
        );
    }

    public function listForNextFriday(int $mosqueId, ?string $status = null, ?string $category = null): array
    {
        $friday = now()->isFriday() ? now()->copy() : now()->next('Friday');

        $tasks = $this->repository->findForDate($mosqueId, $friday, $status, $category);

        return array_merge(
            $this->buildResult($tasks),
            ['date' => $friday->toDateString()]
        );
    }

    private function buildResult(Collection $tasks): array
    {
        $total = $tasks->count();
        $completed = $tasks->where('is_completed', true)->count();

        return [
            'tasks'       => $tasks,
            'total'       => $total,
            'completed'   => $completed,
            'percentage'  => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
            'by_category' => $tasks->groupBy(fn(MosqueTask $t) => $t->category->value)->map->count(),
        ];
    }
}
