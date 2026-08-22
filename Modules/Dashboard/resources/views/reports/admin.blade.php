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

    <div class="section-title">الإحصائيات العامة</div>
    <table class="cards">
        <tr>
            <td><div class="card-value">{{ $data['totals']['mosques'] ?? 0 }}</div><div class="card-label">المساجد</div></td>
            <td><div class="card-value">{{ $data['totals']['students'] ?? 0 }}</div><div class="card-label">الطلاب</div></td>
            <td><div class="card-value">{{ $data['totals']['halaqas'] ?? 0 }}</div><div class="card-label">الحلقات</div></td>
            <td><div class="card-value">{{ $data['totals']['teachers'] ?? 0 }}</div><div class="card-label">المعلمون</div></td>
        </tr>
        <tr>
            <td><div class="card-value">{{ $data['totals']['volunteers'] ?? 0 }}</div><div class="card-label">المتطوعون</div></td>
            <td><div class="card-value">{{ $data['totals']['managers'] ?? 0 }}</div><div class="card-label">مديرو المساجد</div></td>
            <td><div class="card-value">{{ $data['totals']['supervisors'] ?? 0 }}</div><div class="card-label">المشرفون</div></td>
            <td><div class="card-value">{{ $data['totals']['parents'] ?? 0 }}</div><div class="card-label">أولياء الأمور</div></td>
        </tr>
    </table>

    <div class="section-title">مؤشرات المنطقة</div>
    <table class="cards">
        <tr>
            <td><div class="card-value">{{ $data['totals']['mosques'] ?? 0 }}</div><div class="card-label">المساجد</div></td>
            <td><div class="card-value">{{ $data['pending_sermons'] ?? 0 }}</div><div class="card-label">الخطب المعلقة</div></td>
            <td><div class="card-value">{{ number_format($data['donations'] ?? 0) }} ر.س</div><div class="card-label">إجمالي التبرعات</div></td>
            <td><div class="card-value">{{ $data['complaints']['urgent'] ?? 0 }}</div><div class="card-label">الشكاوى العاجلة</div></td>
        </tr>
    </table>

    <div class="section-title">المالية والشكاوى والصيانة</div>
    <table class="data">
        <tbody>
            <tr><td>إجمالي التبرعات (مكتملة)</td><td>{{ number_format($data['donations'] ?? 0) }} ر.س</td></tr>
            <tr><td>إجمالي الشكاوى</td><td>{{ $data['complaints']['total'] ?? 0 }}</td></tr>
            <tr><td>الشكاوى المعلقة</td><td>{{ $data['complaints']['pending'] ?? 0 }}</td></tr>
            <tr><td>إجمالي طلبات الصيانة</td><td>{{ $data['maintenance']['total'] ?? 0 }}</td></tr>
            <tr><td>طلبات الصيانة المعلقة</td><td>{{ $data['maintenance']['pending'] ?? 0 }}</td></tr>
        </tbody>
    </table>

    <div class="footer">تم توليد هذا التقرير آلياً من نظام إدارة المسجد</div>
</div>
</body>
</html>
