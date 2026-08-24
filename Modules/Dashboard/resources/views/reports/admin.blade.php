<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقرير مدير المنطقة</title>
    <style>
        @font-face {
            font-family: 'Cairo';
            font-style: normal;
            font-weight: normal;
            src: url('/tmp/Cairo-Regular.ttf') format('truetype');
        }
        @font-face {
            font-family: 'Cairo';
            font-style: normal;
            font-weight: bold;
            src: url('/tmp/Cairo-Bold.ttf') format('truetype');
        }

        body {
            font-family: 'Cairo', sans-serif;
            direction: rtl;
            margin: 0;
            padding: 0;
            color: #334155;
        }

        .page { padding: 24px; }

        .header {
            text-align: center;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 14px;
            margin-bottom: 24px;
        }
        .header h1 { font-size: 24px; margin: 0 0 6px; color: #0f172a; }
        .header .meta { font-size: 12px; color: #64748b; }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            border-right: 6px solid #2563eb;
            padding-right: 10px;
            margin: 26px 0 14px;
            color: #0f172a;
        }

        .cards {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px;
        }
        .cards td {
            width: 25%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            vertical-align: top;
        }
        .card-value { font-size: 22px; font-weight: bold; color: #0f172a; }
        .card-label { font-size: 12px; color: #64748b; margin-top: 6px; }

        table.data {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        table.data th, table.data td {
            border: 1px solid #e2e8f0;
            padding: 8px 10px;
            text-align: right;
        }
        table.data th { background: #eff6ff; color: #1e3a8a; }

        .footer { margin-top: 30px; text-align: center; font-size: 11px; color: #94a3b8; }
    </style>
</head>
<body>
<div class="page">

    <div class="header">
        <h1>تقرير لوحة تحكم مدير المنطقة (النظام ككل)</h1>
        <div class="meta">
            المدير: {{ $user_name }} &nbsp;|&nbsp;
            تاريخ التوليد: {{ $generated_at }}
        </div>
    </div>

    <div class="section-title">بطاقات لوحة التحكم</div>
    <table class="cards">
        <tr>
            <td><div class="card-value">{{ $data['mosques_of_region'] ?? 0 }}</div><div class="card-label">مساجد المنطقة</div></td>
            <td><div class="card-value">{{ $data['mosques_under_maintenance'] ?? 0 }}</div><div class="card-label">مساجد تحت الصيانة</div></td>
            <td><div class="card-value">{{ $data['pending_sermons'] ?? 0 }}</div><div class="card-label">الخطب المعلقة</div></td>
            <td><div class="card-value">{{ number_format($data['region_donations_this_month']['total_base_amount'] ?? 0) }} ر.س</div><div class="card-label">تبرعات المساجد (هذا الشهر)</div></td>
            <td><div class="card-value">{{ $data['critical_complaints'] ?? 0 }}</div><div class="card-label">الشكاوى الحرجة</div></td>
        </tr>
    </table>

    <div class="section-title">تفاصيل تبرعات المساجد (هذا الشهر)</div>
    <table class="data">
        <tbody>
            <tr><td>عدد التبرعات</td><td>{{ $data['region_donations_this_month']['count'] ?? 0 }}</td></tr>
            <tr><td>إجمالي المبلغ (بالليرة)</td><td>{{ number_format($data['region_donations_this_month']['total_base_amount'] ?? 0) }} ر.س</td></tr>
            <tr><td>إجمالي المبلغ (العملة الأصلية)</td><td>{{ number_format($data['region_donations_this_month']['total_amount'] ?? 0) }} {{ $data['region_donations_this_month']['currency'] ?? '' }}</td></tr>
            <tr><td>الحملات النشطة</td><td>{{ $data['region_donations_this_month']['active_campaigns'] ?? 0 }}</td></tr>
        </tbody>
    </table>

    <div class="footer">تم توليد هذا التقرير آلياً من نظام إدارة المسجد</div>
</div>
</body>
</html>
