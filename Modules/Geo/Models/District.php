<?php

namespace Modules\Geo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// use Modules\Geo\Database\Factories\DistrictFactory;

class District extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'city_id',
        'name_ar',
        'name_en',
        'lat',
        'lng',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    // protected static function newFactory(): DistrictFactory
    // {
    //     // return DistrictFactory::new();
    // }
}
