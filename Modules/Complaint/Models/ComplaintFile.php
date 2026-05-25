<?php

namespace Modules\Complaint\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Complaint\Database\Factories\ComplaintFileFactory;

class ComplaintFile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['complaint_id', 'file', 'file_type'];

    public function complaint() {
        return $this->belongsTo(Complaint::class);
    }



    // protected static function newFactory(): ComplaintFileFactory
    // {
    //     // return ComplaintFileFactory::new();
    // }
}
