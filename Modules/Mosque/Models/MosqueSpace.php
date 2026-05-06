<?php

namespace Modules\Mosque\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Mosque\Database\Factories\MosqueSpaceFactory;

class MosqueSpace extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['mosque_id', 'name', 'capacity'];

     /**
     * Get the mosque that owns the space.
     */
    public function mosque()
    {
        return $this->belongsTo(Mosque::class);
    }

    // protected static function newFactory(): MosqueSpaceFactory
    // {
    //     // return MosqueSpaceFactory::new();
    // }
}
