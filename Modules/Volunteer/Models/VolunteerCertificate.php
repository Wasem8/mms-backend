<?php

namespace Modules\Volunteer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\User\Models\User;
// use Modules\Volunteer\Database\Factories\VolunteerCertificateFactory;

class VolunteerCertificate extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'volunteer_certificates';

    protected $fillable = [
        'volunteer_id',
        'opportunity_id',
        'certificate_url',
        'issued_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
    ];

    public function volunteer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'volunteer_id');
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(VolunteerOpportunity::class, 'opportunity_id');
    }
}
