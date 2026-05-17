<?php

namespace Modules\Education\Events;

use Illuminate\Queue\SerializesModels;

class AttendanceRecorded
{
    use SerializesModels;

    public $records;
    public $halaqaId;
    public $date;

    public function __construct(array $records, $halaqaId, $date)
    {
        $this->records = $records;
        $this->halaqaId = $halaqaId;
        $this->date = $date;
    }
}
