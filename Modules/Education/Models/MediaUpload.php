<?php

namespace Modules\Education\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\User\Models\User;

// use Modules\Education\Database\Factories\MediaUploadFactory;

class MediaUpload extends Model
{
    protected $fillable = [
        'user_id',
        'path',
        'type',
        'url',
    ];

    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }
}
