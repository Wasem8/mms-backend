<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقرير المشرف التربوي</title>

    <style>
        @font-face {
            font-family: 'Arial';
            src: url({{ base_path('public/fonts/Arial.ttf') }}) format('truetype');
        }

        body {
            font-family: 'Arial', DejaVu Sans, sans-serif;
            direction: rtl;
            text-align: right;
            margin: 0;
            padding: 30px;
            color: #222;
            background: #fff;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .title {
            font-size: 28px;
            font-weight: bold;
            color: #d62828;
            margin-bottom: 10px;
        }

        .sub-title {
            font-size: 14px;
            color: #555;
        }

        .info-box {
            width: 100%;
            margin-bottom: 25px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            background: #f9f9f9;
        }

        .info-row {
            margin-bottom: 8px;
            font-size: 14px;
        }

        .info-label {
            font-weight: bold;
            color: #111;
        }

        .cards {
            width: 100%;
            margin-bottom: 30px;
        }

        .card {
            width: 48%;
            display: inline-block;
            vertical-align: top;
            margin-bottom: 15px;
            padding: 20px;
            border-radius: 10px;
            color: white;
            text-align: center;
        }

        .blue {
            background: #2563eb;
        }

        .green {
            background: #16a34a;
        }

        .orange {
            background: #ea580c;
        }

        .red {
            background: #dc2626;
        }

        .card-title {
            font-size: 15px;
            margin-bottom: 10px;
        }

        .card-value {
            font-size: 32px;
            font-weight: bold;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin: 25px 0 15px;
            border-right: 5px solid #d62828;
            padding-right: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        table thead {
            background: #d62828;
            color: white;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 12px;
            font-size: 13px;
            text-align: center;
        }

        table tbody tr:nth-child(even) {
            background: #f5f5f5;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }

        .signature {
            margin-top: 60px;
        }

        .signature-box {
            width: 45%;
            display: inline-block;
            text-align: center;
        }

        .signature-line {
            margin-top: 50px;
            border-top: 1px solid #333;
            padding-top: 8px;
            width: 80%;
            margin-right: auto;
            margin-left: auto;
        }
    </style>
</head>
<body>

<div class="header">
    <div class="title">
        تقرير المشرف التربوي
    </div>

    <div class="sub-title">
        تقرير إحصائي شامل للمتابعة والإشراف
    </div>
</div>

<div class="info-box">

    <div class="info-row">
        <span class="info-label">اسم المشرف:</span>
        {{ $user->name ?? 'غير معروف' }}
    </div>

    <div class="info-row">
        <span class="info-label">البريد الإلكتروني:</span>
        {{ $user->email ?? '-' }}
    </div>

    <div class="info-row">
        <span class="info-label">تاريخ إنشاء التقرير:</span>
        {{ now()->format('Y-m-d H:i') }}
    </div>

</div>

<div class="cards">

    <div class="card blue">
        <div class="card-title">
            عدد الحلقات
        </div>

        <div class="card-value">
            {{ $stats['halaqat_count'] ?? 0 }}
        </div>
    </div>

    <div class="card green">
        <div class="card-title">
            عدد الطلاب
        </div>

        <div class="card-value">
            {{ $stats['students_count'] ?? 0 }}
        </div>
    </div>

    <div class="card orange">
        <div class="card-title">
            عدد المعلمين
        </div>

        <div class="card-value">
            {{ $stats['teachers_count'] ?? 0 }}
        </div>
    </div>

    <div class="card red">
        <div class="card-title">
            نسبة الحضور
        </div>

        <div class="card-value">
            {{ $stats['attendance_rate'] ?? 0 }}%
        </div>
    </div>

</div>

<div class="section-title">
    تفاصيل الحلقات
</div>

<table>
    <thead>
    <tr>
        <th>#</th>
        <th>اسم الحلقة</th>
        <th>المعلم</th>
        <th>عدد الطلاب</th>
        <th>نسبة الحضور</th>
    </tr>
    </thead>

    <tbody>

    @forelse(($stats['halaqat'] ?? []) as $index => $halaqa)

        <tr>
            <td>{{ $index + 1 }}</td>

            <td>
                {{ $halaqa['name'] ?? '-' }}
            </td>

            <td>
                {{ $halaqa['teacher_name'] ?? '-' }}
            </td>

            <td>
                {{ $halaqa['students_count'] ?? 0 }}
            </td>

            <td>
                {{ $halaqa['attendance_rate'] ?? 0 }}%
            </td>
        </tr>

    @empty

        <tr>
            <td colspan="5">
                لا توجد بيانات متاحة
            </td>
        </tr>

    @endforelse

    </tbody>
</table>

<div class="section-title">
    ملاحظات المشرف
</div>

<div class="info-box">
    <p style="line-height: 2;">
        تم إنشاء هذا التقرير بشكل آلي من خلال نظام إدارة الحلقات،
        ويحتوي على ملخص كامل للأداء والإحصائيات الخاصة بالمشرف التربوي.
    </p>
</div>

<div class="signature">

    <div class="signature-box">
        <div class="signature-line">
            توقيع المشرف
        </div>
    </div>

    <div class="signature-box">
        <div class="signature-line">
            اعتماد الإدارة
        </div>
    </div>

</div>

<div class="footer">
    جميع الحقوق محفوظة © {{ date('Y') }}
</div>

</body>
</html>
