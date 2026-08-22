<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>بطاقة المسجد التعريفية</title>
    <style>
        @font-face { font-family: 'Cairo'; font-style: normal; font-weight: normal; src: url('/tmp/Cairo-Regular.ttf') format('truetype'); }
        @font-face { font-family: 'Cairo'; font-style: normal; font-weight: bold; src: url('/tmp/Cairo-Bold.ttf') format('truetype'); }
        body { font-family: 'Cairo', sans-serif; direction: rtl; margin: 0; padding: 0; color: #334155; }
        .page { padding: 24px; }
        .header { text-align: center; border-bottom: 3px solid #2563eb; padding-bottom: 14px; margin-bottom: 24px; }
        .header h1 { font-size: 22px; margin: 0 0 6px; color: #0f172a; }
        .header .meta { font-size: 12px; color: #64748b; }
        .section-title { font-size: 18px; font-weight: bold; border-right: 6px solid #2563eb; padding-right: 10px; margin: 26px 0 14px; color: #0f172a; }
        .info { width: 100%; border-collapse: collapse; font-size: 13px; }
        .info td { border: 1px solid #e2e8f0; padding: 9px 11px; }
        .info td:first-child { background: #f1f5f9; font-weight: bold; width: 30%; color: #1e3a8a; }
        table.data { width: 100%; border-collapse: collapse; font-size: 12px; }
        table.data th, table.data td { border: 1px solid #e2e8f0; padding: 7px 9px; text-align: right; }
        table.data th { background: #eff6ff; color: #1e3a8a; }
        .footer { margin-top: 30px; text-align: center; font-size: 11px; color: #94a3b8; }
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <h1>بطاقة المسجد التعريفية: {{ $data['mosque']['name'] }}</h1>
        <div class="meta">المعد بواسطة: {{ $user_name }} ({{ $user_role }}) &nbsp;|&nbsp; {{ $generated_at }}</div>
    </div>

    <table class="info">
        <tr><td>الحالة التشغيلية</td><td>{{ $data['mosque']['status'] }}</td></tr>
        <tr><td>العنوان</td><td>{{ $data['mosque']['address'] }}</td></tr>
        <tr><td>الموقع الجغرافي (GPS)</td><td>{{ $data['mosque']['gps'] }}</td></tr>
        <tr><td>مدير المسجد</td><td>{{ $data['mosque']['manager'] }}</td></tr>
        <tr><td>الإمام</td><td>{{ $data['mosque']['imam'] }}</td></tr>
        <tr><td>الخطيب</td><td>{{ $data['mosque']['khatib'] }}</td></tr>
    </table>

    <div class="section-title">القاعات والمساحات</div>
    <table class="data">
        <thead><tr><th>القاعة</th><th>السعة (مصلٍ)</th></tr></thead>
        <tbody>
        @forelse ($data['mosque']['spaces'] as $s)
            <tr><td>{{ $s['name'] }}</td><td>{{ number_format($s['capacity']) }}</td></tr>
        @empty
            <tr><td colspan="2">لا توجد قاعات مسجلة</td></tr>
        @endforelse
        </tbody>
    </table>

    <div class="section-title">المرافق</div>
    <p>{{ count($data['mosque']['facilities']) ? implode('، ', $data['mosque']['facilities']) : 'لا توجد مرافق مسجلة' }}</p>

    <div class="section-title">الاحتياجات</div>
    <table class="data">
        <thead><tr><th>العنوان</th><th>النوع</th><th>الحالة</th></tr></thead>
        <tbody>
        @forelse ($data['mosque']['needs'] as $n)
            <tr><td>{{ $n['title'] }}</td><td>{{ $n['type'] }}</td><td>{{ $n['status'] }}</td></tr>
        @empty
            <tr><td colspan="3">لا توجد احتياجات</td></tr>
        @endforelse
        </tbody>
    </table>

    <div class="footer">تم توليد هذا التقرير آلياً من نظام إدارة المسجد</div>
</div>
</body>
</html>
