<?php

namespace Modules\Invitation\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Mosque\Models\Mosque;
use Modules\User\Models\User;

class Invitation extends Model
{
    protected $fillable = [
        'email',
        'role',
        'created_by',
        'mosque_id',
        'token',
        'expires_at',
        'accepted_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    protected $appends = ['status', 'status_label'];


    public function getStatusAttribute(): string
    {
        if ($this->accepted_at !== null) {
            return 'accepted';
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'expired';
        }

        return 'pending';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'accepted' => 'مقبولة',
            'expired'  => 'منتهية الصلاحية',
            'pending'  => 'معلقة',
            default    => 'غير معروفة',
        };
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isValid(): bool
    {
        return !$this->accepted_at &&
            (!$this->expires_at || now()->lessThan($this->expires_at));
    }

    public function mosque() {
        return $this->belongsTo(Mosque::class, 'mosque_id');
    }
}
