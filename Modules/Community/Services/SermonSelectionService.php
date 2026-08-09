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
use Modules\Community\Events\SermonSelectedForFriday;

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
                'sermon_id' => __('messages.community.sermon_not_found'),
            ]);
        }

        if ($sermon->status !== 'Archived') {
            throw ValidationException::withMessages([
                'sermon_id' => __('messages.community.only_archived_sermon'),
            ]);
        }


        $selection = $this->selectionRepo->upsert($mosqueManager->id, $dto->sermonId, $dto->fridayDate);

        event(new SermonSelectedForFriday($sermon, $selection, $mosqueManager));

        return $selection;
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
                'id' => __('messages.community.selection_not_found_or_unauthorized'),
            ]);
        }

        if ($selection->friday_date->isPast()) {
            throw ValidationException::withMessages([
                'id' => __('messages.community.cannot_cancel_past_selection'),
            ]);
        }

        $this->selectionRepo->delete($selection);
    }


}
