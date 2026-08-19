<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>تقرير التبرعات</title>
    <style>
        body { font-family: 'Cairo', sans-serif; font-size: 12px; color: #222; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .meta { color: #666; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: right; }
        th { background: #f3f4f6; }
        .summary { margin-top: 16px; }
        .summary span { display: inline-block; margin-left: 18px; }
        .footer { margin-top: 24px; color: #999; font-size: 10px; }
    </style>
</head>
<body>
    <h1>تقرير التبرعات — {{ $mosque_name }}</h1>
    <div class="meta">تاريخ الإصدار: {{ $generated_at }}</div>

    <div class="summary">
        <span>إجمالي المبلغ: {{ number_format($summary['total_amount'], 2) }} {{ $summary['currency'] }}</span>
        <span>عدد التبرعات: {{ $summary['total_count'] }}</span>
        <span>مبلغ النقدي: {{ number_format($summary['cash_amount'], 2) }}</span>
        <span>تبرعات العينية: {{ $summary['in_kind_count'] }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                @if ($showMosque)
                    <th>المسجد</th>
                @endif
                <th>المتبرع</th>
                <th>النوع</th>
                <th>الطريقة</th>
                <th>المبلغ</th>
                <th>العملة</th>
                <th>الحالة</th>
                <th>الحملة</th>
                <th>التاريخ</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($donations as $d)
                <tr>
                    <td>{{ $d['id'] }}</td>
                    @if ($showMosque)
                        <td>{{ $d['mosque_name'] ?? '—' }}</td>
                    @endif
                    <td>{{ $d['donor_name'] }}</td>
                    <td>{{ $d['donation_type'] === 'cash' ? 'نقدي' : 'عيني' }}</td>
                    <td>{{ $d['payment_method'] }}</td>
                    <td>{{ number_format((float) $d['base_amount'], 2) }}</td>
                    <td>{{ $d['currency'] }}</td>
                    <td>{{ $d['status'] }}</td>
                    <td>{{ $d['campaign'] ?? '—' }}</td>
                    <td>{{ $d['created_at'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $showMosque ? 11 : 10 }}" style="text-align:center;">
                        لا توجد تبرعات مطابقة للفلاتر.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">تم إنشاء هذا التقرير آلياً عبر نظام إدارة المساجد.</div>
</body>
</html>
