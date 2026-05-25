<?php
namespace Modules\Complaint\DTO;

use Illuminate\Http\UploadedFile;

readonly class CreateMaintenanceRequestDTO
{
    public function __construct(
        public int    $mosqueId,
        public string $title,
        public string $description,
        public string $category,
        public string $urgency,
        public array  $attachments = [],
    ) {}
}
