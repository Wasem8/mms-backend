<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>التقرير الإحصائي الشامل لمساجد المنطقة</title>
    <style>
        @font-face { font-family: 'Cairo'; font-style: normal; font-weight: normal; src: url('/tmp/Cairo-Regular.ttf') format('truetype'); }
        @font-face { font-family: 'Cairo'; font-style: normal; font-weight: bold; src: url('/tmp/Cairo-Bold.ttf') format('truetype'); }
        body { font-family: 'Cairo', sans-serif; direction: rtl; margin: 0; padding: 0; color: #334155; }
        .page { padding: 24px; }
        .header { text-align: center; border-bottom: 3px solid #2563eb; padding-bottom: 14px; margin-bottom: 24px; }
        .header h1 { font-size: 22px; margin: 0 0 6px; color: #0f172a; }
        .header .meta { font-size: 12px; color: #64748b; }
        .section-title { font-size: 18px; font-weight: bold; border-right: 6px solid #2563eb; padding-right: 10px; margin: 26px 0 14px; color: #0f172a; }
        .cards { width: 100%; border-collapse: separate; border-spacing: 10px; }
        .cards td { width: 50%; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; text-align: center; }
        .card-value { font-size: 22px; font-weight: bold; color: #0f172a; }
        .card-label { font-size: 12px; color: #64748b; margin-top: 6px; }
        table.data { width: 100%; border-collapse: collapse; font-size: 12px; }
        table.data th, table.data td { border: 1px solid #e2e8f0; padding: 7px 9px; text-align: right; }
        table.data th { background: #eff6ff; color: #1e3a8a; }
        .footer { margin-top: 30px; text-align: center; font-size: 11px; color: #94a3b8; }
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <h1>التقرير الإحصائي الشامل لمساجد المنطقة</h1>
        <div class="meta">المدير: {{ $user_name }} &nbsp;|&nbsp; تاريخ التوليد: {{ $generated_at }}</div>
    </div>

    <table class="cards">
        <tr>
            <td><div class="card-value">{{ $data['total_mosques'] }}</div><div class="card-label">إجمالي المساجد</div></td>
            <td><div class="card-value">{{ number_format($data['total_capacity']) }}</div><div class="card-label">إجمالي السعة (مصلٍ)</div></td>
        </tr>
    </table>

    <div class="section-title">التوزيع حسب المدينة</div>
    <table class="data">
        <thead><tr><th>المدينة</th><th>عدد المساجد</th><th>السعة</th><th>نشط</th><th>صيانة</th><th>قيد الإنشاء</th></tr></thead>
        <tbody>
        @foreach ($data['by_city'] as $c)
            <tr>
                <td>{{ $c['city'] }}</td>
                <td>{{ $c['count'] }}</td>
                <td>{{ number_format($c['capacity']) }}</td>
                <td>{{ $c['active'] }}</td>
                <td>{{ $c['maintenance'] }}</td>
                <td>{{ $c['construction'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="section-title">تفصيل المساجد</div>
    <table class="data">
        <thead><tr><th>الاسم</th><th>المدينة/الحي</th><th>الحالة</th><th>المدير</th><th>الإمام</th><th>الخطيب</th><th>السعة</th><th>المرافق</th><th>الموقع</th></tr></thead>
        <tbody>
        @foreach ($data['mosques'] as $m)
            <tr>
                <td>{{ $m['name'] }}</td>
                <td>{{ $m['city'] }} / {{ $m['district'] }}</td>
                <td>{{ $m['status'] }}</td>
                <td>{{ $m['manager'] }}</td>
                <td>{{ $m['imam'] }}</td>
                <td>{{ $m['khatib'] }}</td>
                <td>{{ number_format($m['capacity']) }}</td>
                <td>{{ implode('، ', $m['facilities']) }}</td>
                <td>{{ $m['gps'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="footer">تم توليد هذا التقرير آلياً من نظام إدارة المسجد</div>
</div>
</body>
</html>
