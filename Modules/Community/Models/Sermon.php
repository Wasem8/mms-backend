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

    protected $table = 'sermons';
    protected $fillable = ['title', 'content', 'speaker_name', 'sermon_date', 'status', 'notes', 'mosque_manager_id', 'region_manager_id'];

    public function attachments() {
        return $this->hasMany(SermonAttachement::class);
    }

    public function mosqueManager() {
        return $this->belongsTo(User::class, 'mosque_manager_id');
    }

    public function regionManager() {
        return $this->belongsTo(User::class, 'region_manager_id');
    }



    // protected static function newFactory(): SermonFactory
    // {
    //     // return SermonFactory::new();
    // }
}
