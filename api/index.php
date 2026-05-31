<?php

putenv('TMPDIR=/tmp');
$_ENV['TMPDIR'] = '/tmp';
$_SERVER['TMPDIR'] = '/tmp';

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

// ربط الـ Facade بالـ app أولاً
\Illuminate\Support\Facades\Facade::setFacadeApplication($app);

// تشغيل النواة
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // تشغيل migrate مباشرة عبر الـ app بدون Facade
    $artisan = $app->make(\Illuminate\Contracts\Console\Kernel::class);

    ob_start();
    $exitCode = $artisan->call('migrate', ['--force' => true]);
    $migrationLog = ob_get_clean();

    // أو استخدم Artisan بعد التأكد من الـ bootstrap
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'تم بنجاح ✅',
        'exit_code' => $exitCode,
        'migration_log' => $migrationLog
    ], JSON_UNESCAPED_UNICODE);
    exit;

} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'حدث خطأ ❌',
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
