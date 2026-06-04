<?php

namespace Modules\Volunteer\Enums;

enum ApplicationStatus: string
{
    case Pending  = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
