<?php

namespace Modules\Community\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Community\Database\Factories\TameemRecipientFactory;

class TameemRecipient extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'tameem_recipients';
    protected $fillable = ['tameem_id', 'mosque_manager_id', 'is_read', 'read_at'];

     public function tameem() {
        return $this->belongsTo(Tameem::class, 'tameem_id');
    }

    // protected static function newFactory(): TameemRecipientFactory
    // {
    //     // return TameemRecipientFactory::new();
    // }
}
