<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            direction: rtl;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }

        th {
            background: #f2f2f2;
        }

        .stats {
            margin-bottom: 20px;
        }

        .stats div {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>

<h2>تقرير المشرف</h2>

<div class="stats">
    <div>عدد الحلقات: {{ $halaqat_count }}</div>
    <div>عدد الطلاب: {{ $students_count }}</div>
    <div>عدد المعلمين: {{ $teachers_count }}</div>
    <div>نسبة الحضور: {{ $attendance_rate }}%</div>
</div>

<table>
    <thead>
    <tr>
        <th>الحلقة</th>
        <th>المعلم</th>
        <th>عدد الطلاب</th>
        <th>نسبة الحضور</th>
    </tr>
    </thead>
    <tbody>
    @foreach($halaqat as $halaqa)
        <tr>
            <td>{{ $halaqa['name'] }}</td>
            <td>{{ $halaqa['teacher'] }}</td>
            <td>{{ $halaqa['students_count'] }}</td>
            <td>{{ $halaqa['attendance_rate'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<p style="margin-top: 30px">
    تاريخ إنشاء التقرير:
    {{ now()->format('Y-m-d H:i') }}
</p>

</body>
</html>
