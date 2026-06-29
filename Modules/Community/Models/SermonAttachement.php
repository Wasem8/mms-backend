<?php

namespace Modules\Community\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Community\Database\Factories\SermonAttachementFactory;

class SermonAttachement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */

    protected $table = 'sermon_attachments';
    protected $fillable = ['sermon_id', 'file_path', 'file_type'];

     public function sermon() {
        return $this->belongsTo(Sermon::class);
    }
    

    // protected static function newFactory(): SermonAttachementFactory
    // {
    //     // return SermonAttachementFactory::new();
    // }
}
