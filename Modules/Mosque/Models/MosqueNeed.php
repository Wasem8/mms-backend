<?php

namespace Modules\Mosque\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// use Modules\Mosque\Database\Factories\MosqueNeedFactory;

class MosqueNeed extends Model
{
    use HasFactory;


    protected $fillable = [
        'mosque_id',
        'title',
        'description',
        'type',
        'target_amount',
        'collected_amount',
        'status',
        'image',
        'is_urgent',
    ];

    public function mosque(): BelongsTo
    {
        return $this->belongsTo(Mosque::class);
    }

}
