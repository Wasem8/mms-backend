<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'XBRiyaz', 'DejaVu Sans', sans-serif;
            direction: rtl;
            text-align: right;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 24px;
        }
        .meta-info {
            float: left;
            font-size: 12px;
            color: #7f8c8d;
        }
        .stats-boxes {
            margin-bottom: 30px;
            width: 100%;
        }
        .card {
            display: inline-block;
            width: 22%;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 15px;
            text-align: center;
            border-radius: 5px;
            margin-left: 2%;
        }
        .card h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #7f8c8d;
        }
        .card .number {
            font-size: 20px;
            font-weight: bold;
            color: #2e7d32;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #bdc3c7;
            padding: 10px;
            text-align: center;
            font-size: 13px;
        }
        th {
            background-color: #2c3e50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #95a5a6;
            border-top: 1px solid #ecf0f1;
            padding-top: 5px;
        }
    </style>
</head>
<body>

<div class="header">
    <span class="meta-info">تاريخ التصدير: {{ $date }}</span>
    <h1>نظام إدارة الحلقات القرآني الذكي</h1>
    <h2>تقرير متابعة إحصائيات المشرف العام</h2>
</div>

<div class="stats-boxes">
    <div class="card">
        <h3>إجمالي الحلقات (UC-44)</h3>
        <div class="number">{{ $stats['total_halaqats'] }}</div>
    </div>
    <div class="card">
        <h3>إجمالي الطلاب</h3>
        <div class="number">{{ $stats['total_students'] }}</div>
    </div>
    <div class="card">
        <h3>الطلاب النشطين</h3>
        <div class="number">{{ $stats['active_students'] }}</div>
    </div>
    <div class="card">
        <h3>طلبات معلقة (UC-45)</h3>
        <div class="number" style="color: #d32f2f;">{{ $stats['pending_requests'] }}</div>
    </div>
</div>

<hr style="border: 0; border-top: 1px dashed #ccc; margin: 20px 0;">

<h3>📊 بيان تفصيلي بالحلقات القائمة</h3>
<table>
    <thead>
    <tr>
        <th>رقم الحلقة</th>
        <th>اسم الحلقة (UC-44)</th>
        <th>عدد المعلمين المسندين</th>
        <th>عدد الطلاب المسجلين</th>
        <th>تاريخ الإنشاء</th>
    </tr>
    </thead>
    <tbody>
    @foreach($halaqats as $halaqa)
        <tr>
            <td>{{ $halaqa->id }}</td>
            <td>{{ $halaqa->name }}</td>
            <td>{{ $halaqa->teachers_count }} معلمين</td>
            <td>{{ $halaqa->students_count }} طلاب</td>
            <td>{{ $halaqa->created_at?->format('Y-m-d') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="footer">
    تقرير مؤتمت صادر عن لوحة تحكم المشرف - جميع الحقوق محفوظة © {{ date('Y') }}
</div>

</body>
</html>
