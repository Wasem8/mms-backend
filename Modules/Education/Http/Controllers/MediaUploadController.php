<?php

namespace Modules\Education\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Education\Services\MediaUploadService;

class MediaUploadController extends Controller
{
    public function __construct(
        private MediaUploadService $service
    ) {}

    public function uploadVoice(
        Request $request
    ) {

        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:mp3,wav,m4a,ogg'
            ]
        ]);

        $upload = $this->service
            ->uploadVoice(
                $request->file('file')
            );

        return ApiResponse::success([
            'id' => $upload->id,
            'url' => $upload->url,
        ]);
    }
}
