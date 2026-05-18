<?php

namespace Modules\Donation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Mosque\Models\Mosque;
use Modules\Mosque\Models\MosqueNeed;

// use Modules\Donation\Database\Factories\DonationFactory;

class Donation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */

    protected $table = 'donations';
    protected $fillable = ['reference', 'mosque_id', 'mosque_need_id', 'campaign_id', 'user_id', 'type', 'amount', 'item_description', 'donor_name', 'status', 'completed_at'];


    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
    public function mosque()
    {
        return $this->belongsTo(Mosque::class);
    }

    public function mosqueNeed()
    {
        return $this->belongsTo(MosqueNeed::class);
    }

    // protected static function newFactory(): DonationFactory
    // {
    //     // return DonationFactory::new();
    // }
}
