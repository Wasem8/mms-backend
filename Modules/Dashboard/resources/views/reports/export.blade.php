<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">

    <style>

        @page {
            margin: 18px 18px 25px 18px;
        }

        body {
            font-family: cairo;
            direction: rtl;
            font-size: 11px;
            color: #1f2937;
            line-height: 1.6;
        }

        .header {
            text-align: center;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 2px solid #d1d5db;
        }

        .system-name {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 2px;
        }

        .title {
            font-size: 21px;
            font-weight: bold;
            margin: 2px 0 4px;
        }

        .subtitle {
            font-size: 10px;
            color: #6b7280;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .meta-table td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
        }

        .meta-label {
            font-weight: bold;
        }

        .summary-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .summary-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
            margin: 0 -6px 12px;
        }

        .card {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: center;
            background: #f8fafc;
        }

        .card-title {
            font-size: 9px;
            color: #6b7280;
        }

        .card-value {
            font-size: 18px;
            font-weight: bold;
            margin-top: 2px;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            margin: 14px 0 7px;
            padding-bottom: 4px;
            border-bottom: 1px solid #d1d5db;
        }

        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .stats-table th,
        .stats-table td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            text-align: center;
        }

        .stats-table th {
            background: #f3f4f6;
            font-weight: bold;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .report-table th {
            background: #111827;
            color: white;
            border: 1px solid #111827;
            padding: 7px 6px;
            text-align: center;
            font-size: 10px;
        }

        .report-table td {
            border: 1px solid #d1d5db;
            padding: 6px;
            text-align: center;
            font-size: 10px;
        }

        .report-table tr:nth-child(even) td {
            background: #f9fafb;
        }

        .empty {
            text-align: center !important;
            padding: 15px !important;
            color: #6b7280;
        }

        .recommendations-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .recommendations-table td {
            border: 1px solid #d1d5db;
            padding: 8px 10px;
            font-size: 10px;
            text-align: right;
        }

        .rec-critical {
            border-right: 4px solid #dc2626;
            background: #fef2f2;
        }

        .rec-warning {
            border-right: 4px solid #d97706;
            background: #fffbeb;
        }

        .rec-info {
            border-right: 4px solid #2563eb;
            background: #eff6ff;
        }

        .footer {
            margin-top: 18px;
            padding-top: 8px;
            border-top: 1px solid #d1d5db;
            text-align: center;
            font-size: 8px;
            color: #6b7280;
        }

        .page-break {
            page-break-before: always;
        }

    </style>
</head>

<body>

{{-- =========================================================
     Header
========================================================= --}}

<div class="header">

    <div class="system-name">
        نظام إدارة المساجد والحلقات
    </div>

    <div class="title">
        {{ $title }}
    </div>

    <div class="subtitle">
        تقرير رسمي صادر عن النظام
    </div>

</div>


{{-- =========================================================
     Metadata
========================================================= --}}

<table class="meta-table">

    <tr>

        <td>
            <span class="meta-label">تاريخ إنشاء التقرير:</span>
            {{ $generatedAt->format('Y-m-d H:i') }}
        </td>

        <td>

            <span class="meta-label">معد التقرير:</span>

            {{ $user?->name ?? 'النظام' }}

        </td>

    </tr>

    <tr>

        <td>

            <span class="meta-label">الفترة:</span>

            {{ $filters['date_from'] ?? 'من البداية' }}

            -

            {{ $filters['date_to'] ?? 'حتى الآن' }}

        </td>

        <td>

            <span class="meta-label">المسجد:</span>

            @if(!empty($filters['mosque_id']))
                {{ $rows->first()[1] ?? ('مسجد #' . $filters['mosque_id']) }}
            @else
                جميع المساجد
            @endif

        </td>

    </tr>

</table>


{{-- =========================================================
     Maintenance Summary
========================================================= --}}

@if($slug === 'maintenance')

    <div class="summary-title">
        ملخص طلبات الصيانة
    </div>

    <table class="summary-table">

        <tr>

            <td class="card">
                <div class="card-title">
                    إجمالي الطلبات
                </div>

                <div class="card-value">
                    {{ $summary['total'] ?? 0 }}
                </div>
            </td>

            <td class="card">
                <div class="card-title">
                    قيد الانتظار
                </div>

                <div class="card-value">
                    {{ $summary['pending'] ?? 0 }}
                </div>
            </td>

            <td class="card">
                <div class="card-title">
                    قيد التنفيذ
                </div>

                <div class="card-value">
                    {{ $summary['in_progress'] ?? 0 }}
                </div>
            </td>

            <td class="card">
                <div class="card-title">
                    مكتملة
                </div>

                <div class="card-value">
                    {{ $summary['completed'] ?? 0 }}
                </div>
            </td>

            <td class="card">
                <div class="card-title">
                    عالية الأولوية
                </div>

                <div class="card-value">
                    {{ $summary['high_priority'] ?? 0 }}
                </div>
            </td>

            <td class="card">
                <div class="card-title">
                    نسبة الإنجاز
                </div>

                <div class="card-value">
                    {{ $summary['completion_percentage'] ?? 0 }}%
                </div>
            </td>

        </tr>

    </table>


    {{-- حالات الطلبات --}}

    <div class="section-title">
        توزيع الطلبات حسب الحالة
    </div>

    <table class="stats-table">

        <thead>

        <tr>
            <th>الحالة</th>
            <th>العدد</th>
        </tr>

        </thead>

        <tbody>

        <tr>
            <td>قيد الانتظار</td>
            <td>
                {{ $summary['by_status']['pending'] ?? 0 }}
            </td>
        </tr>

        <tr>
            <td>قيد التنفيذ</td>
            <td>
                {{ $summary['by_status']['in_progress'] ?? 0 }}
            </td>
        </tr>

        <tr>
            <td>مكتمل</td>
            <td>
                {{ $summary['by_status']['completed'] ?? 0 }}
            </td>
        </tr>

        <tr>
            <td>ملغى</td>
            <td>
                {{ $summary['by_status']['cancelled'] ?? 0 }}
            </td>
        </tr>

        </tbody>

    </table>


    {{-- الأولويات --}}

    <div class="section-title">
        توزيع الطلبات حسب الأولوية
    </div>

    <table class="stats-table">

        <thead>

        <tr>
            <th>الأولوية</th>
            <th>العدد</th>
        </tr>

        </thead>

        <tbody>

        <tr>
            <td>منخفضة</td>
            <td>
                {{ $summary['by_priority']['low'] ?? 0 }}
            </td>
        </tr>

        <tr>
            <td>متوسطة</td>
            <td>
                {{ $summary['by_priority']['medium'] ?? 0 }}
            </td>
        </tr>

        <tr>
            <td>عالية</td>
            <td>
                {{ $summary['by_priority']['high'] ?? 0 }}
            </td>
        </tr>

        <tr>
            <td>عاجلة</td>
            <td>
                {{ $summary['by_priority']['urgent'] ?? 0 }}
            </td>
        </tr>

        </tbody>

    </table>

@endif


{{-- =========================================================
     Donations Summary
========================================================= --}}

@if($slug === 'donations')

    <div class="summary-title">
        ملخص التبرعات
    </div>

    <table class="summary-table">

        <tr>

            <td class="card">

                <div class="card-title">
                    عدد التبرعات
                </div>

                <div class="card-value">
                    {{ $summary['count'] ?? 0 }}
                </div>

            </td>

            <td class="card">

                <div class="card-title">
                    إجمالي المبلغ
                </div>

                <div class="card-value">
                    {{ number_format($summary['total_amount'] ?? 0, 2) }}
                </div>

            </td>

            <td class="card">

                <div class="card-title">
                    بالليرة السورية
                </div>

                <div class="card-value">
                    {{ number_format($summary['total_base_amount'] ?? 0, 2) }}
                </div>

            </td>

        </tr>

    </table>

@endif


{{-- =========================================================
     Complaints Summary
========================================================= --}}

@if($slug === 'complaints')

    <div class="summary-title">
        ملخص الشكاوى
    </div>

    <table class="summary-table">

        <tr>

            <td class="card">

                <div class="card-title">
                    إجمالي الشكاوى
                </div>

                <div class="card-value">
                    {{ $summary['total'] ?? 0 }}
                </div>

            </td>

            <td class="card">

                <div class="card-title">
                    قيد الانتظار
                </div>

                <div class="card-value">
                    {{ $summary['pending'] ?? 0 }}
                </div>

            </td>

            <td class="card">

                <div class="card-title">
                    قيد المعالجة
                </div>

                <div class="card-value">
                    {{ $summary['in_progress'] ?? 0 }}
                </div>

            </td>

            <td class="card">

                <div class="card-title">
                    تم الحل
                </div>

                <div class="card-value">
                    {{ $summary['resolved'] ?? 0 }}
                </div>

            </td>

            <td class="card">

                <div class="card-title">
                    مغلقة
                </div>

                <div class="card-value">
                    {{ $summary['closed'] ?? 0 }}
                </div>

            </td>

            <td class="card">

                <div class="card-title">
                    نسبة الإنجاز
                </div>

                <div class="card-value">
                    {{ $summary['completion_percentage'] ?? 0 }}%
                </div>

            </td>

        </tr>

    </table>

    <div class="section-title">
        توزيع الشكاوى حسب الحالة
    </div>

    <table class="stats-table">

        <thead>
        <tr>
            <th>الحالة</th>
            <th>العدد</th>
        </tr>
        </thead>

        <tbody>

        <tr>
            <td>قيد الانتظار</td>
            <td>
                {{ $summary['by_status']['pending'] ?? 0 }}
            </td>
        </tr>

        <tr>
            <td>قيد المعالجة</td>
            <td>
                {{ $summary['by_status']['in_progress'] ?? 0 }}
            </td>
        </tr>

        <tr>
            <td>تم الحل</td>
            <td>
                {{ $summary['by_status']['resolved'] ?? 0 }}
            </td>
        </tr>

        <tr>
            <td>مغلقة</td>
            <td>
                {{ $summary['by_status']['closed'] ?? 0 }}
            </td>
        </tr>

        <tr>
            <td>مرفوضة</td>
            <td>
                {{ $summary['by_status']['rejected'] ?? 0 }}
            </td>
        </tr>

        </tbody>

    </table>

    <div class="section-title">
        توزيع الشكاوى حسب الأولوية
    </div>

    <table class="stats-table">

        <thead>
        <tr>
            <th>الأولوية</th>
            <th>العدد</th>
        </tr>
        </thead>

        <tbody>

        <tr>
            <td>منخفضة</td>
            <td>
                {{ $summary['by_priority']['low'] ?? 0 }}
            </td>
        </tr>

        <tr>
            <td>متوسطة</td>
            <td>
                {{ $summary['by_priority']['medium'] ?? 0 }}
            </td>
        </tr>

        <tr>
            <td>عالية</td>
            <td>
                {{ $summary['by_priority']['high'] ?? 0 }}
            </td>
        </tr>

        <tr>
            <td>عاجلة</td>
            <td>
                {{ $summary['by_priority']['urgent'] ?? 0 }}
            </td>
        </tr>

        </tbody>

    </table>

@endif


{{-- =========================================================
     Details
========================================================= --}}

<div class="section-title">
    تفاصيل السجلات
</div>

<table class="report-table">

    <thead>

    <tr>

        @foreach($columns as $column)

            <th>
                {{ $column }}
            </th>

        @endforeach

    </tr>

    </thead>

    <tbody>

    @forelse($rows as $row)

        <tr>

            @foreach($row as $cell)

                <td>
                    {{ $cell }}
                </td>

            @endforeach

        </tr>

    @empty

        <tr>

            <td
                class="empty"
                colspan="{{ count($columns) }}"
            >
                لا توجد سجلات مطابقة للفلاتر المحددة.
            </td>

        </tr>

    @endforelse

    </tbody>

</table>


{{-- =========================================================
     Recommendations
========================================================= --}}

@if(!empty($recommendations))

    <div class="section-title">
        التوصيات
    </div>

    <table class="recommendations-table">

        <tbody>

        @foreach($recommendations as $rec)

            <tr>
                <td class="rec-{{ $rec['severity'] ?? 'info' }}">
                    {{ $rec['text'] }}
                </td>
            </tr>

        @endforeach

        </tbody>

    </table>

@endif


{{-- =========================================================
     Footer
========================================================= --}}

<div class="footer">

    تم إنشاء هذا التقرير آلياً بواسطة نظام إدارة المساجد والحلقات

</div>

</body>

</html>
