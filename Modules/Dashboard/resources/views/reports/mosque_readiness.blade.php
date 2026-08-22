<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقرير جاهزية المساجد للمواسم</title>
    <style>
        @font-face { font-family: 'Cairo'; font-style: normal; font-weight: normal; src: url('/tmp/Cairo-Regular.ttf') format('truetype'); }
        @font-face { font-family: 'Cairo'; font-style: normal; font-weight: bold; src: url('/tmp/Cairo-Bold.ttf') format('truetype'); }
        body { font-family: 'Cairo', sans-serif; direction: rtl; margin: 0; padding: 0; color: #334155; }
        .page { padding: 24px; }
        .header { text-align: center; border-bottom: 3px solid #2563eb; padding-bottom: 14px; margin-bottom: 24px; }
        .header h1 { font-size: 22px; margin: 0 0 6px; color: #0f172a; }
        .header .meta { font-size: 12px; color: #64748b; }
        .section-title { font-size: 18px; font-weight: bold; border-right: 6px solid #2563eb; padding-right: 10px; margin: 26px 0 14px; color: #0f172a; }
        table.data { width: 100%; border-collapse: collapse; font-size: 12px; }
        table.data th, table.data td { border: 1px solid #e2e8f0; padding: 7px 9px; text-align: right; }
        table.data th { background: #eff6ff; color: #1e3a8a; }
        .ready { color: #16a34a; font-weight: bold; }
        .follow { color: #d97706; font-weight: bold; }
        .footer { margin-top: 30px; text-align: center; font-size: 11px; color: #94a3b8; }
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <h1>تقرير جاهزية المساجد للمواسم (رمضان، التراويح، العيدين، الجمعة)</h1>
        <div class="meta">المدير: {{ $user_name }} &nbsp;|&nbsp; تاريخ التوليد: {{ $generated_at }}</div>
    </div>

    <table class="data">
        <thead>
            <tr><th>المسجد</th><th>السعة</th><th>المرافق</th><th>طلبات صيانة مفتوحة</th><th>الجاهزية</th></tr>
        </thead>
        <tbody>
        @forelse ($data['mosques'] as $m)
            <tr>
                <td>{{ $m['name'] }}</td>
                <td>{{ number_format($m['capacity']) }}</td>
                <td>{{ implode('، ', $m['facilities']) }}</td>
                <td>{{ $m['open_maintenance'] }}</td>
                <td class="{{ $m['readiness'] === 'جاهز' ? 'ready' : 'follow' }}">{{ $m['readiness'] }}</td>
            </tr>
        @empty
            <tr><td colspan="5">لا توجد مساجد ضمن التقرير</td></tr>
        @endforelse
        </tbody>
    </table>

    <div class="footer">تم توليد هذا التقرير آلياً من نظام إدارة المسجد</div>
</div>
</body>
</html>
