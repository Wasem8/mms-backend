<?php

namespace Modules\Community\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\User\Models\User;

// use Modules\Community\Database\Factories\TameemFactory;

class Tameem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'tameems';
    protected $fillable = ['title', 'content', 'sender_id', 'sent_at'];

     public function sender() {
        return $this->belongsTo(User::class, 'sender_id');
    }
    public function recipients()
    {
        return $this->belongsToMany(User::class, 'tameem_recipients', 'tameem_id', 'mosque_manager_id')
            ->withPivot('is_read')
            ->withTimestamps();
    }


    // protected static function newFactory(): TameemFactory
    // {
    //     // return TameemFactory::new();
    // }
}
