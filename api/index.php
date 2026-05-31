<?php

// 1. إصلاح مسار التخزين المؤقت في Vercel
putenv('TMPDIR=/tmp');
$_ENV['TMPDIR'] = '/tmp';
$_SERVER['TMPDIR'] = '/tmp';

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// ==========================================
// 💣 الخيار النووي: تجاوز الموجه (Router)
// ==========================================
try {
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');

    // إجبار تحديث قاعدة البيانات لحل مشكلة priority
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    $migrationLog = \Illuminate\Support\Facades\Artisan::output();

    // إرجاع النتيجة فوراً وإيقاف التطبيق
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'تم اختراق الكاش وتحديث قاعدة البيانات بنجاح! ✅',
        'migration_log' => $migrationLog
    ], JSON_UNESCAPED_UNICODE);
    exit; // إيقاف التنفيذ هنا

} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'حدث خطأ ❌',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ... باقي الكود (لن يصل إليه حالياً)
