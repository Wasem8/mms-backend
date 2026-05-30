<?php

namespace Modules\MaintenanceRequest\Enums;


enum MaintenanceCategory: string
{
    case Electrical = 'electrical';
    case Plumbing   = 'plumbing';
    case Carpentry  = 'carpentry';
    case Cleaning   = 'cleaning';
    case Other      = 'other';
}
