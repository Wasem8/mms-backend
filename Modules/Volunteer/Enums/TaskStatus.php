<?php

namespace Modules\Volunteer\Enums;

enum TaskStatus: string
{
    case Unassigned = 'unassigned';
    case Assigned   = 'assigned';
    case Completed  = 'completed';
}
