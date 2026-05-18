<?php

namespace Modules\Community\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Community\Database\Factories\ProgramScheduleFactory;

class ProgramSchedule extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */

    protected $table = 'program_schedules';
    protected $fillable = ['dawah_program_id', 'title', 'notes', 'date', 'start_time', 'end_time'];

    public function program()
    {
        return $this->belongsTo(DawahProgram::class, 'dawah_program_id');
    }

    // protected static function newFactory(): ProgramScheduleFactory
    // {
    //     // return ProgramScheduleFactory::new();
    // }
}
