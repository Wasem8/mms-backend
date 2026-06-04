<?php

namespace Modules\Volunteer\Repositories\Eloquent;

use Modules\Volunteer\DTOs\LogHoursDTO;
use Modules\Volunteer\Models\VolunteerCertificate;
use Modules\Volunteer\Models\VolunteerLog;
use Modules\Volunteer\Repositories\Contracts\VolunteerEvaluationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentVolunteerEvaluationRepository implements VolunteerEvaluationRepositoryInterface
{
    public function __construct(
        private readonly VolunteerLog $logModel,
        private readonly VolunteerCertificate $certModel,
    ) {}

    #[\Override]
    public function findLogById(int $id): ?VolunteerLog
    {
        return $this->logModel->find($id);
    }

    #[\Override]
    public function findLogsByVolunteer(int $volunteerId): Collection
    {
        return $this->logModel
            ->where('volunteer_id', $volunteerId)
            ->with('opportunity')
            ->latest()
            ->get();
    }

    #[\Override]
    public function createLog(LogHoursDTO $dto): VolunteerLog
    {
        return $this->logModel->create([
            'volunteer_id'       => $dto->volunteerId,
            'opportunity_id'     => $dto->opportunityId,
            'logged_hours'       => $dto->loggedHours,
            'manager_evaluation' => $dto->managerEvaluation,
            'notes'              => $dto->notes,
        ]);
    }

    #[\Override]
    public function totalHours(int $volunteerId, int $opportunityId): float
    {
        return (float) $this->logModel
            ->where('volunteer_id', $volunteerId)
            ->where('opportunity_id', $opportunityId)
            ->sum('logged_hours');
    }

    #[\Override]
    public function issueCertificate(int $volunteerId, int $opportunityId, string $url): VolunteerCertificate
    {
        return $this->certModel->create([
            'volunteer_id'    => $volunteerId,
            'opportunity_id'  => $opportunityId,
            'certificate_url' => $url,
            'issued_at'       => now(),
        ]);
    }

    #[\Override]
    public function findCertificate(int $volunteerId, int $opportunityId): ?VolunteerCertificate
    {
        return $this->certModel
            ->where('volunteer_id', $volunteerId)
            ->where('opportunity_id', $opportunityId)
            ->first();
    }

    #[\Override]
    public function findCertificatesByVolunteer(int $volunteerId): Collection
    {
        return $this->certModel
            ->where('volunteer_id', $volunteerId)
            ->with('opportunity')
            ->latest('issued_at')
            ->get();
    }
}
