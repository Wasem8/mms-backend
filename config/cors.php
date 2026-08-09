<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    // 1. أضف 'profile' و 'profile/*' كي يغطي مسارات البروفايل أو استخدم '*' ليشمل كل المسارات
    'paths' => ['*'],

    'allowed_methods' => ['*'],

    // 2. حدد الـ Origins بدلاً من '*' إذا كنت قد تحتاج مفتاح Credentials في Swagger
    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
