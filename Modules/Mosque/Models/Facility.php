<?php

namespace Modules\Mosque\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// use Modules\Mosque\Database\Factories\FacilityFactory;

class Facility extends Model
{
    use HasFactory;

    protected $table = 'facilities';

    protected $fillable = [
        'name',
    ];

    public function mosques(): BelongsToMany
    {
        return $this->belongsToMany(Mosque::class, 'facility_mosque');
    }
}
