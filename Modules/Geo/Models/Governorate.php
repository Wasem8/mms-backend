<?php

namespace Modules\Geo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

// use Modules\Geo\Database\Factories\GovernorateFactory;

class Governorate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name_ar',
        'name_en',
        'latitude',
        'longitude',
    ];

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function districts(): HasManyThrough
    {
        return $this->hasManyThrough(District::class, City::class);
    }
    // protected static function newFactory(): GovernorateFactory
    // {
    //     // return GovernorateFactory::new();
    // }
}
