<?php

namespace Modules\Mosque\Repositories;

use Illuminate\Support\Collection;
use Modules\Mosque\DTOs\CreateMosqueTaskDTO;
use Modules\Mosque\DTOs\UpdateMosqueTaskDTO;
use Modules\Mosque\Models\MosqueTask;
use Illuminate\Support\Carbon;

class EloquentMosqueTaskRepository implements MosqueTaskRepositoryInterface
{
    public function __construct(private readonly MosqueTask $model) {}


    public function findById(int $mosqueId, int $id): ?MosqueTask
    {
        return $this->model
            ->where('mosque_id', $mosqueId)
            ->find($id);
    }

    public function findForDate(int $mosqueId, Carbon $date, ?string $status = null, ?string $category = null): Collection
    {
        return $this->model
            ->where('mosque_id', $mosqueId)
            ->whereDate('due_date', $date)
            ->when($status === 'completed', fn($q) => $q->where('is_completed', true))
            ->when($status === 'pending', fn($q) => $q->where('is_completed', false))
            ->when($category, fn($q) => $q->where('category', $category))
            ->orderBy('due_time')
            ->get();
    }

    public function countsByDate(int $mosqueId, array $dates): array
    {
        // $dates = ['today' => Carbon, 'tomorrow' => Carbon, ...]
        $result = [];

        foreach ($dates as $label => $date) {
            $result[$label] = $this->model
                ->where('mosque_id', $mosqueId)
                ->whereDate('due_date', $date)
                ->count();
        }

        return $result;
    }

    public function create(CreateMosqueTaskDTO $dto): MosqueTask
    {
        return $this->model->create([
            'mosque_id'   => $dto->mosqueId,
            'created_by'  => $dto->createdBy,
            'title'       => $dto->title,
            'category'    => $dto->category,
            'due_date'    => $dto->dueDate,
            'due_time'    => $dto->dueTime,
            'is_completed' => false,
            'is_important' => $dto->isImportant,
            'notes'       => $dto->notes,
        ]);
    }


    public function update(MosqueTask $task, UpdateMosqueTaskDTO $dto): MosqueTask
    {
        $task->update(array_filter([
            'title'        => $dto->title,
            'category'     => $dto->category,
            'due_date'     => $dto->dueDate,
            'due_time'     => $dto->dueTime,
            'is_important' => $dto->isImportant,
            'notes'        => $dto->notes,
        ], fn($v) => $v !== null));

        return $task->fresh();
    }

    public function toggleComplete(MosqueTask $task): MosqueTask
    {
        $task->update([
            'is_completed' => ! $task->is_completed,
            'completed_at' => ! $task->is_completed ? now() : null,
        ]);

        return $task->fresh();
    }

    public function delete(MosqueTask $task): bool
    {
        return $task->delete();
    }
}
