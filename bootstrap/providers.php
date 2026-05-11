<?php

use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    \Modules\User\Providers\UserServiceProvider::class,
    \Modules\Invitation\Providers\InvitationServiceProvider::class,

];
