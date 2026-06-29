<?php

namespace Modules\Education\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Education\Models\Student;

class StudentRejected
{
    use SerializesModels;

    public function __construct(public Student $student) {}
}
