<?php

namespace Modules\Mosque\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Mosque\Enums\MosqueTaskCategory;
use Modules\User\Models\User;
use Modules\Mosque\Traits\HasPostgresBooleans;

class MosqueTask extends Model
{
    use HasFactory;
    use HasPostgresBooleans;


    /**
     * The attributes that are mass assignable.
     */
   protected $fillable = [
        'mosque_id',
        'created_by',
        'title',
        'category',
        'due_date',
        'due_time',
        'is_completed',
        'completed_at',
        'is_important',
        'notes',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'is_important' => 'boolean',
        'category'      => MosqueTaskCategory::class,
        'due_date'      => 'date',
        'completed_at'  => 'datetime',
    ];

    public function mosque(): BelongsTo
    {
        return $this->belongsTo(\Modules\Mosque\Models\Mosque::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
