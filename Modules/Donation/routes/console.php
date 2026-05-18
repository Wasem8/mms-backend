<?php

namespace Modules\routes;

use Illuminate\Support\Facades\Schedule;

Schedule::command('campaigns:expire')->hourly();
