<?php

// 1. إعداد مسار التخزين المؤقت
putenv('TMPDIR=/tmp');
$_ENV['TMPDIR'] = '/tmp';
$_SERVER['TMPDIR'] = '/tmp';

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 2. إيقاظ نواة لارافل بشكل كامل لكي تعمل أوامر الـ Artisan
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // 3. تحديث قاعدة البيانات إجبارياً لحل مشكلة priority
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    $migrationLog = \Illuminate\Support\Facades\Artisan::output();

    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'تم الاتصال بقاعدة البيانات وتحديثها بنجاح! ✅',
        'migration_log' => $migrationLog
    ], JSON_UNESCAPED_UNICODE);
    exit;
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'حدث خطأ ❌',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
