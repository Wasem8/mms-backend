<?php

namespace Modules\Community\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(
            related: User::class,
            table: 'tameem_recipients',
            foreignPivotKey: 'tameem_id',
            relatedPivotKey: 'mosque_manager_id',
        )->withPivot('is_read', 'read_at')
            ->withTimestamps();
    }


    // protected static function newFactory(): TameemFactory
    // {
    //     // return TameemFactory::new();
    // }
}
