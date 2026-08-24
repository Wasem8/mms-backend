<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقرير مدير المسجد</title>
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
            font-size: 12px;
        }
        table.data th, table.data td {
            border: 1px solid #e2e8f0;
            padding: 8px 10px;
            text-align: right;
        }
        table.data th { background: #eff6ff; color: #1e3a8a; }

        .list { font-size: 13px; line-height: 1.9; }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 8px;
            font-size: 11px;
            background: #e2e8f0;
            color: #334155;
        }
        .footer { margin-top: 30px; text-align: center; font-size: 11px; color: #94a3b8; }
    </style>
</head>
<body>
<div class="page">

    <div class="header">
        <h1>تقرير لوحة تحكم مدير المسجد</h1>
        <div class="meta">
            المسجد: {{ $mosque_name }} &nbsp;|&nbsp;
            المدير: {{ $user_name }} &nbsp;|&nbsp;
            تاريخ التوليد: {{ $generated_at }}
        </div>
    </div>

    <div class="section-title">المؤشرات الرئيسية</div>
    <table class="cards">
        <tr>
            <td>
                <div class="card-value">{{ $data['kpi_cards']['monthly_donations']['formatted_value'] ?? 0 }}</div>
                <div class="card-label">تبرعات الشهر</div>
            </td>
            <td>
                <div class="card-value">{{ $data['kpi_cards']['open_maintenance_requests']['value'] ?? 0 }}</div>
                <div class="card-label">طلبات صيانة مفتوحة</div>
            </td>
            <td>
                <div class="card-value">{{ $data['kpi_cards']['complaints']['value'] ?? 0 }}</div>
                <div class="card-label">شكاوى مفتوحة</div>
            </td>
            <td>
                <div class="card-value">{{ $data['kpi_cards']['accredited_volunteers']['value'] ?? 0 }}</div>
                <div class="card-label">متطوعون معتمدون</div>
            </td>
        </tr>
    </table>

    <div class="section-title">أحدث الأنشطة</div>
    <div class="list">
        @forelse ($data['recent_activities'] ?? [] as $activity)
            <div>• {{ $activity['title'] ?? '' }} <span class="badge">{{ $activity['time_ago'] ?? '' }}</span></div>
        @empty
            <div>لا توجد أنشطة</div>
        @endforelse
    </div>

    <div class="section-title">أحدث البلاغات والطلبات</div>
    <table class="data">
        <thead>
            <tr><th>العنوان</th><th>النوع</th><th>الأولوية</th><th>الحالة</th></tr>
        </thead>
        <tbody>
            @forelse ($data['latest_requests'] ?? [] as $req)
                <tr>
                    <td>{{ $req['title'] ?? '' }}</td>
                    <td>{{ $req['type'] ?? '' }}</td>
                    <td>{{ $req['priority'] ?? '' }}</td>
                    <td>{{ $req['status'] ?? '' }}</td>
                </tr>
            @empty
                <tr><td colspan="4">لا توجد بلاغات</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">إحصائيات المسجد</div>
    <table class="data">
        <tbody>
            <tr><td>الطلاب</td><td>{{ $stats['total_students'] ?? 0 }}</td></tr>
            <tr><td>المعلمون</td><td>{{ $stats['total_teachers'] ?? 0 }}</td></tr>
            <tr><td>المتطوعون</td><td>{{ $stats['total_volunteers'] ?? 0 }}</td></tr>
            <tr><td>الدعوات المعلقة</td><td>{{ $stats['pending_invitations'] ?? 0 }}</td></tr>
            <tr><td>التبرعات المعتمدة</td><td>{{ number_format($stats['donations'] ?? 0) }} ر.س</td></tr>
            <tr><td>طلبات الصيانة المفتوحة</td><td>{{ $stats['open_maintenance_requests'] ?? 0 }}</td></tr>
            <tr><td>البلاغات والشكاوى المفتوحة</td><td>{{ $stats['complaints'] ?? 0 }}</td></tr>
            <tr><td>المتطوعون المعتمدون</td><td>{{ $stats['accredited_volunteers'] ?? 0 }}</td></tr>
        </tbody>
    </table>

    <div class="section-title">التوصيات التشغيلية</div>
    @if (!empty($recommendations))
        <table class="data">
            <thead>
                <tr>
                    <th>التصنيف</th>
                    <th>الأهمية</th>
                    <th>التوصية</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($recommendations as $rec)
                    <tr>
                        <td>{{ $rec['category'] ?? '' }}</td>
                        <td>
                            @if (($rec['severity'] ?? '') === 'info')
                                معلومات
                            @elseif (($rec['severity'] ?? '') === 'warning')
                                تنبيه
                            @elseif (($rec['severity'] ?? '') === 'critical')
                                حرج
                            @else
                                {{ $rec['severity'] ?? '' }}
                            @endif
                        </td>
                        <td>{{ $rec['text'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="list">لا توجد توصيات حالياً — جميع المؤشرات ضمن الحدود المقبولة.</div>
    @endif

    <div class="footer">تم توليد هذا التقرير آلياً من نظام إدارة المسجد</div>
</div>
</body>
</html>
