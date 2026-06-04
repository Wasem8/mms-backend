<?php

namespace Modules\Volunteer\Enums;

enum TaskStatus: string
{
    case Assigned  = 'assigned';
    case Completed = 'completed';
}
