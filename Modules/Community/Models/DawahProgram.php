<?php

namespace Modules\Community\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Mosque\Models\Mosque;
use Modules\Mosque\Models\MosqueSpace;

// use Modules\Community\Database\Factories\DawahProgramFactory;

class DawahProgram extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */

    protected $table = 'dawah_programs';
    protected $fillable = ['mosque_id', 'space_id', 'program_name', 'description', 'type', 'image', 'presenter', 'presenter_image', 'is_featured', 'status', 'level'];


    public function mosque()
    {
        return $this->belongsTo(Mosque::class);
    }

    public function space()
    {
        return $this->belongsTo(MosqueSpace::class, 'space_id');
    }

    public function schedules()
    {
        return $this->hasMany(ProgramSchedule::class);
    }
    // protected static function newFactory(): DawahProgramFactory
    // {
    //     // return DawahProgramFactory::new();
    // }
}
