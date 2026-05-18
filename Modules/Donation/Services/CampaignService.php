<?php

namespace Modules\Donation\Services;

use Illuminate\Support\Facades\Http;
use Modules\Donation\Repositories\CampaignRepositoryInterface;

class CampaignService
{
    protected $campaignRepository;

    public function __construct(CampaignRepositoryInterface $campaignRepository)
    {
        $this->campaignRepository = $campaignRepository;
    }

    public function getAllCampaigns()
    {
        $this->campaignRepository->expirePastEndDateCampaigns();
        return $this->campaignRepository->all();
    }

    public function getCampaignById($id)
    {
        $this->campaignRepository->expirePastEndDateCampaigns();
        return $this->campaignRepository->find($id);
    }

    public function getCampaignsByMosque($mosqueId)
    {
        $this->campaignRepository->expirePastEndDateCampaigns();
        return $this->campaignRepository->findByMosque($mosqueId);
    }
    public function getStatsByMosque(int $mosqueId): array
    {
        return $this->campaignRepository->getStatsByMosque($mosqueId);
    }

    public function createCampaign(array $data)
    {
        if (isset($data['cover_image'])) {
            $data['cover_image'] = $this->uploadImage($data['cover_image']);
        }
        return $this->campaignRepository->create($data);
    }

    public function updateCampaign($id, array $data)
    {
        if (isset($data['cover_image'])) {
            $data['cover_image'] = $this->uploadImage($data['cover_image']);
        }
        return $this->campaignRepository->update($id, $data);
    }

    public function deleteCampaign($id)
    {        $campaign = $this->campaignRepository->find($id);
        return $this->campaignRepository->delete($$campaign);
    }

    public function expireEndedCampaigns(): int
    {
        return $this->campaignRepository->expirePastEndDateCampaigns();
    }

    private function uploadImage($image): string
    {
        $fileName = uniqid() . '.' . $image->getClientOriginalExtension();

        $baseUrl = config('services.supabase.url');
        $bucket = config('services.supabase.bucket');
        $key = config('services.supabase.key');

        $path = $bucket . '/' . $fileName;

        $uploadUrl = $baseUrl . '/storage/v1/object/' . $path;

        $response = Http::withHeaders([
            'apikey' => $key,
            'Authorization' => 'Bearer ' . $key,
        ])->attach(
            'file',
            file_get_contents($image),
            $fileName
        )->post($uploadUrl);

        if (!$response->successful()) {
            throw new \Exception('Upload failed: ' . $response->body());
        }

        return $baseUrl . '/storage/v1/object/public/' . $path;
    }

    private function deleteImage(string $url): void
    {
        $bucket = env('SUPABASE_BUCKET');

        $fileName = basename($url);

        $path = $bucket . '/' . $fileName;

        $deleteUrl = env('SUPABASE_URL') . '/storage/v1/object/' . $path;

        Http::withHeaders([
            'apikey' => env('SUPABASE_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_KEY'),
        ])->delete($deleteUrl);
    }
}


