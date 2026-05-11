<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Cloudinary\Cloudinary;



class CloudinaryService
{
    private Cloudinary $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary(config('app.cloudinary_url'));
    }

    public function upload(?UploadedFile $file, string $folder = 'mosques'): ?string
    {
        if (!$file) {
            return null;
        }

        $result = $this->cloudinary->uploadApi()->upload(
            $file->getRealPath(),
            [
                'folder' => $folder
            ]
        );

        return $result['secure_url'] ?? null;
    }
}
