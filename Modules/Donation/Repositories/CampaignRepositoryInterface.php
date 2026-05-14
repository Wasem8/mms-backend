<?php

namespace Modules\Donation\Repositories;

interface CampaignRepositoryInterface
{
    public function all();

    public function find($id);

    public function findByMosque($mosqueId);

    public function create(array $data);

    public function update($id, array $data);

    public function delete($id);

    public function expirePastEndDateCampaigns();

    public function getStatsByMosque(int $mosqueId): array;
}
