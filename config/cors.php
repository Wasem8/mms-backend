<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    // المسارات التي سيطبق عليها الـ CORS
    // أضفنا '*' لتشمل api/ و education/ و auth/ وأي مسار آخر
    'paths' => ['api/*', 'auth/*', 'education/*','dashboard/*', 'sanctum/csrf-cookie'],

    // الطرق المسموح بها (GET, POST, PUT, DELETE, etc.)
    'allowed_methods' => ['*'],

    // النطاقات المسموح لها بالوصول (Origins)
    // في التطوير نضع '*' للسماح للكل (بما في ذلك Swagger و Localhost)
    'allowed_origins' => ['*'],

    // إذا كنت تستخدم أنماط معينة للنطاقات
    'allowed_origins_patterns' => [],

    // الـ Headers المسموح للمتصفح بإرسالها
    // يجب أن تشمل Authorization لإرسال التوكن و Content-Type للبيانات
    'allowed_headers' => ['*'],

    // الـ Headers التي يمكن للمتصفح رؤيتها في الرد
    'exposed_headers' => [],

    // كم من الوقت (بالثواني) يجب تخزين نتيجة فحص الـ CORS في المتصفح
    'max_age' => 0,

    // هل تسمح بإرسال ملفات الارتباط (Cookies/Credentials)
    // اجعلها false إذا كنت تعتمد فقط على الـ JWT Token في الـ Header
    'supports_credentials' => false,

];
