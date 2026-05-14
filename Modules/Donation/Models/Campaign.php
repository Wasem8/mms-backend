<?php

namespace Modules\Donation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Mosque\Models\Mosque;

// use Modules\Donation\Database\Factories\CampaignFactory;

class Campaign extends Model
{
    use HasFactory;

    /**
     * The attributes
     * that are mass assignable.
     */

    protected $table = 'campaigns';
    protected $fillable = ['mosque_id','title', 'description', 'target_amount', 'collected_amount', 'status', 'start_date', 'end_date', 'cover_image','priority'];

    // protected static function newFactory(): CampaignFactory
    // {
    //     // return CampaignFactory::new();
    // }

    public function donations() {
        return $this->hasMany(Donation::class);
    }

    public function mosque()
    {
        return $this->belongsTo(Mosque::class);
    }
}
