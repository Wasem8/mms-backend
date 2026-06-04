<?php

namespace Modules\Volunteer\Repositories\Contracts;

use Modules\Volunteer\DTOs\LogHoursDTO;
use Modules\Volunteer\Models\VolunteerCertificate;
use Modules\Volunteer\Models\VolunteerLog;

interface VolunteerEvaluationRepositoryInterface
{
    public function findLogById(int $id): ?VolunteerLog;

    public function findLogsByVolunteer(int $volunteerId): \Illuminate\Database\Eloquent\Collection;

    public function createLog(LogHoursDTO $dto): VolunteerLog;

    public function totalHours(int $volunteerId, int $opportunityId): float;

    public function issueCertificate(int $volunteerId, int $opportunityId, string $url): VolunteerCertificate;

    public function findCertificate(int $volunteerId, int $opportunityId): ?VolunteerCertificate;

    public function findCertificatesByVolunteer(int $volunteerId): \Illuminate\Database\Eloquent\Collection;
}
