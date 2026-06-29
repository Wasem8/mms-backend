<?php

namespace Modules\Dashboard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Dashboard\Database\Factories\ReportFactory;

class Report extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'type',
        'storage_path',
    ];

    // protected static function newFactory(): ReportFactory
    // {
    //     // return ReportFactory::new();
    // }
}
