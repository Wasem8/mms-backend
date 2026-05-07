<?php

namespace Modules\Mosque\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Modules\Community\Models\DawahProgram;
use Modules\User\Models\User;

// use Modules\Mosque\Database\Factories\MosqueFactory;

class Mosque extends Model
{
    use HasFactory;

    protected $table = 'mosques';

    protected $fillable = [
        'name',
        'image',
        'working_hours',
        'status',
        'is_featured',
        'city',
        'district',
        'latitude',
        'longitude',
        //'place_id',
        'average_rating',
        'reviews_count',
        'imam',
        'khatib',
        'manager_id'
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'average_rating' => 'decimal:2',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image
            ? Storage::url($this->image)
            : null;
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class, 'facility_mosque');
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }
    public function needs()
    {
        return $this->hasMany(MosqueNeed::class);
    }
    public function spaces()
    {
        return $this->hasMany(MosqueSpace::class);
    }

    public function dawahPrograms()
    {
        return $this->hasMany(DawahProgram::class);
    }

}
