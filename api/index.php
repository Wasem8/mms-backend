<?php

// DEBUG ONLY - احذفه بعد التشخيص
if (isset($_GET['debug_server'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'NOT SET',
        'PATH_INFO' => $_SERVER['PATH_INFO'] ?? 'NOT SET',
        'HTTP_X_FORWARDED_URI' => $_SERVER['HTTP_X_FORWARDED_URI'] ?? 'NOT SET',
        'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? 'NOT SET',
        'all_headers' => getallheaders(),
    ]);
    exit;
}

// توجيه الطلبات إلى ملف الاندكس الأصلي في لارفيل
require __DIR__ . '/../public/index.php';
