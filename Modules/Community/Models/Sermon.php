<?php

namespace Modules\Community\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\User\Models\User;

// use Modules\Community\Database\Factories\SermonFactory;

class Sermon extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */

    const CATEGORY_CREED          = 'creed_faith';
    const CATEGORY_JURISPRUDENCE  = 'jurisprudence_rulings';
    const CATEGORY_ETHICS         = 'ethics_conduct';
    const CATEGORY_CONTEMPORARY   = 'contemporary_issues';
    const CATEGORY_OCCASIONS      = 'occasions_seasons';
    const CATEGORY_OTHER          = 'other';

    const CATEGORIES = [
        self::CATEGORY_CREED,
        self::CATEGORY_JURISPRUDENCE,
        self::CATEGORY_ETHICS,
        self::CATEGORY_CONTEMPORARY,
        self::CATEGORY_OCCASIONS,
        self::CATEGORY_OTHER,
    ];

    protected $table = 'sermons';
    protected $fillable = ['title', 'content', 'speaker_name', 'sermon_date', 'status', 'notes', 'mosque_manager_id', 'region_manager_id','category'];

    public function attachments() {
        return $this->hasMany(SermonAttachement::class);
    }

    public function mosqueManager() {
        return $this->belongsTo(User::class, 'mosque_manager_id');
    }

    public function regionManager() {
        return $this->belongsTo(User::class, 'region_manager_id');
    }

    public function selections()
    {
        return $this->hasMany(SermonSelection::class);
    }


    // app/Modules/Community/Models/Sermon.php

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->when($filters['status'] ?? null, fn($q, $status) => $q->where('status', $status))
            ->when($filters['mosque_manager_id'] ?? null, fn($q, $id) => $q->where('mosque_manager_id', $id))
            ->when($filters['region_manager_id'] ?? null, fn($q, $id) => $q->where('region_manager_id', $id))
            ->when($filters['speaker_name'] ?? null, fn($q, $name) => $q->where('speaker_name', 'like', "%{$name}%"))
            ->when($filters['keyword'] ?? null, function ($q, $keyword) {
                $q->where(function ($sub) use ($keyword) {
                    $sub->where('title', 'like', "%{$keyword}%")
                        ->orWhere('content', 'like', "%{$keyword}%")
                        ->orWhere('speaker_name', 'like', "%{$keyword}%");
                });
            })
            // فلترة حسب تاريخ إلقاء الخطبة (sermon_date)
            ->when($filters['sermon_date_from'] ?? null, fn($q, $date) => $q->whereDate('sermon_date', '>=', $date))
            ->when($filters['sermon_date_to'] ?? null, fn($q, $date) => $q->whereDate('sermon_date', '<=', $date))
            // فلترة حسب تاريخ التقديم (created_at)
            ->when($filters['submitted_from'] ?? null, fn($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['submitted_to'] ?? null, fn($q, $date) => $q->whereDate('created_at', '<=', $date));
    }


    // protected static function newFactory(): SermonFactory
    // {
    //     // return SermonFactory::new();
    // }
}
