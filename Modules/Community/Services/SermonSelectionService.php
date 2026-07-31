<?php

namespace Modules\Community\Services;

use Modules\Community\DTOs\SelectSermonForFridayDTO;
use Modules\Community\Models\SermonSelection;
use Modules\Community\Repositories\SermonRepositoryInterface;
use Modules\Community\Repositories\SermonSelectionRepositoryInterface;
use Modules\User\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class SermonSelectionService
{
    public function __construct(
        protected SermonSelectionRepositoryInterface $selectionRepo,
        protected SermonRepositoryInterface $sermonRepo,
    ) {}

    public function selectForFriday(SelectSermonForFridayDTO $dto, User $mosqueManager): SermonSelection
    {
        $sermon = $this->sermonRepo->findById($dto->sermonId);

        if (!$sermon) {
            throw ValidationException::withMessages([
                'sermon_id' => 'الخطبة غير موجودة.',
            ]);
        }

        if ($sermon->status !== 'Archived') {
            throw ValidationException::withMessages([
                'sermon_id' => 'يمكن اختيار خطبة معتمدة (مؤرشفة) فقط.',
            ]);
        }

        return $this->selectionRepo->upsert($mosqueManager->id, $dto->sermonId, $dto->fridayDate);
    }
    public function getMySelections(User $mosqueManager, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $filters['mosque_manager_id'] = $mosqueManager->id;
        return $this->selectionRepo->search($filters, $perPage);
    }

    public function searchSelections(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->selectionRepo->search($filters, $perPage);
    }

    public function getUpcomingPerMosque(): Collection
    {
        return $this->selectionRepo->upcomingPerMosque();
    }

    public function cancelSelection(int $selectionId, User $mosqueManager): void
    {
        $selection = $this->selectionRepo->findById($selectionId);

        if (!$selection || $selection->mosque_manager_id !== $mosqueManager->id) {
            throw ValidationException::withMessages([
                'id' => 'الاختيار غير موجود أو لا تملك صلاحية إلغائه.',
            ]);
        }

        if ($selection->friday_date->isPast()) {
            throw ValidationException::withMessages([
                'id' => 'لا يمكن إلغاء اختيار خطبة تاريخها قد مضى.',
            ]);
        }

        $this->selectionRepo->delete($selection);
    }

    
}
