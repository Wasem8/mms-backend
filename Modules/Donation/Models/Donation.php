<?php

namespace Modules\Donation\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Mosque\Models\Mosque;
use Modules\User\Models\User;

// use Modules\Donation\Database\Factories\DonationFactory;

class Donation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */

    protected $table = 'donations';
    use SoftDeletes;

    protected $fillable = [
        'reference',
        'mosque_id',
        'user_id',
        'campaign_id',
        'mosque_need_id',
        'donation_type',
        'payment_method',
        'amount',
        'item_description',
        'donor_name',
        'stripe_payment_intent_id',
        'status',
        'currency',
        'exchange_rate',
        'base_amount',

    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'base_amount'   => 'decimal:2',
        'completed_at'  => 'datetime',
        'created_at'    => 'datetime',
    ];


    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function mosqueNeed(): BelongsTo
    {
        return $this->belongsTo(\Modules\Mosque\Models\MosqueNeed::class);
    }

    public function mosque(): BelongsTo
    {
        return $this->belongsTo(Mosque::class, 'mosque_id', 'id');
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCash($query)
    {
        return $query->where('donation_type', 'cash');
    }
}
