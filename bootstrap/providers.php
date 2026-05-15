<?php

use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    \Modules\User\Providers\UserServiceProvider::class,
    \Modules\Invitation\Providers\InvitationServiceProvider::class,
    \Modules\Common\Providers\CommonServiceProvider::class,
    \Modules\Education\Providers\EducationServiceProvider::class,
];
