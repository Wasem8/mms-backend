<?php

namespace Modules\Dashboard\Models;

use Illuminate\Database\Eloquent\Model;

class BackupLog extends Model
{
    protected $table = 'backup_logs';

    protected $fillable = [
        'file_name',
        'storage_path',
        'file_size',
        'checksum',
        'status',
        'triggered_by',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
