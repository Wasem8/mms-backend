<?php

namespace Modules\Community\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\User\Models\User;

// use Modules\Community\Database\Factories\SermonSelectionFactory;

class SermonSelection extends Model
{
    use HasFactory;


    protected $table = 'sermon_selections';
    protected $fillable = ['mosque_manager_id', 'sermon_id', 'friday_date'];
    protected $casts = ['friday_date' => 'date'];

    public function sermon()
    {
        return $this->belongsTo(Sermon::class);
    }

    public function mosqueManager()
    {
        return $this->belongsTo(User::class, 'mosque_manager_id');
    }

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->when($filters['mosque_manager_id'] ?? null, fn($q, $id) => $q->where('mosque_manager_id', $id))
            ->when($filters['friday_date_from'] ?? null, fn($q, $d) => $q->whereDate('friday_date', '>=', $d))
            ->when($filters['friday_date_to'] ?? null, fn($q, $d) => $q->whereDate('friday_date', '<=', $d));
    }
}
