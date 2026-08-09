<?php

namespace Modules\Mosque\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Modules\Community\Models\DawahProgram;
use Modules\Donation\Models\Donation;
use Modules\Geo\Models\City;
use Modules\Geo\Models\District;
use Modules\MaintenanceRequest\Models\Maintenance;
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
        'city_id',
        'district_id',
        'latitude',
        'longitude',
        //'place_id',
        'average_rating',
        'reviews_count',
        'imam',
        'khatib',
        'donation_total',
        'manager_id'
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'average_rating' => 'decimal:2',
    ];


    protected $appends = ['image_url', 'has_urgent_need'];

    protected $hidden = ['open_urgent_needs_count'];

    public function getHasUrgentNeedAttribute(): bool
    {
        return (int) ($this->open_urgent_needs_count ?? 0) > 0;
    }

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
        return $this->hasMany(Maintenance::class);
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

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }


    protected static function newFactory()
    {
        return \Modules\Mosque\Database\Factories\MosqueFactory::new();
    }

    public function scopeScopeNearby($query, $latitude, $longitude)
    {
        // معادلة هافرسين لحساب المسافة الجغرافية بالكيلومترات
        return $query->select('*')
            ->selectRaw(
                '( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance',
                [$latitude, $longitude, $latitude]
            )
            ->orderBy('distance', 'asc');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function scopeManagedBy($query, int $userId)
    {
        return $query->where('manager_id', $userId);
    }


}
