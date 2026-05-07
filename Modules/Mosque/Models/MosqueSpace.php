<?php

namespace Modules\Mosque\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Community\Models\DawahProgram;

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

    public function dawahPrograms()
    {
        return $this->hasMany(DawahProgram::class);
    }

    // protected static function newFactory(): MosqueSpaceFactory
    // {
    //     // return MosqueSpaceFactory::new();
    // }
}
