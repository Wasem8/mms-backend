<?php

namespace Modules\Volunteer\Repositories\Contracts;

interface VolunteerStatsRepositoryInterface
{
    public function getCompletionRate(int $mosqueId): float;
}
