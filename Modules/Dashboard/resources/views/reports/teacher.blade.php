<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>

    <meta charset="UTF-8">

    <style>

        body{
            font-family:sans-serif;
            direction:rtl;
            margin:0;
            padding:0;
            color:#1f2937;
        }

        .page{
            padding:25px;
            page-break-after:always;
            box-sizing:border-box;
        }

        .cover{
            min-height:1020px; /* مناسب لـ A4 تقريباً */
            display:flex;
            justify-content:center;
            align-items:center;
            flex-direction:column;
            background:linear-gradient(135deg,#0f172a,#1e293b);
            color:#fff;
            text-align:center;
            box-sizing:border-box;
            page-break-after:always;
            overflow:hidden;
        }

        .cover h1{
            font-size:34px;
            margin-bottom:12px;
        }

        .cover h3{
            margin:8px 0;
            font-size:22px;
        }

        .cover p{
            margin:6px 0;
            font-size:16px;
        }

        .section-title{
            font-size:24px;
            margin-bottom:20px;
            border-right:5px solid #2563eb;
            padding-right:10px;
        }

        .cards{
            display:flex;
            gap:12px;
            flex-wrap:wrap;
        }

        .card{
            flex:1;
            min-width:160px;
            border:1px solid #e5e7eb;
            background:#f8fafc;
            border-radius:14px;
            padding:20px;
            text-align:center;
        }

        .card h3{
            font-size:14px;
            color:#64748b;
            margin:0;
        }

        .card p{
            font-size:26px;
            font-weight:bold;
            margin-top:12px;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        th{
            background:#1e293b;
            color:white;
            padding:12px;
        }

        td{
            border:1px solid #e5e7eb;
            padding:10px;
            text-align:center;
        }

        tr:nth-child(even){
            background:#f8fafc;
        }

        .success{
            color:#166534;
            font-weight:bold;
        }

        .danger{
            color:#991b1b;
            font-weight:bold;
        }

        .warning{
            color:#92400e;
            font-weight:bold;
        }

        .footer{
            text-align:center;
            margin-top:30px;
            color:#94a3b8;
            font-size:12px;
        }

    </style>

</head>

<body>

@if(!$has_halaqa)

    <div class="page">
        <h2>لا يوجد حلقة مرتبطة بهذا المعلم</h2>
    </div>

@else

    <!-- COVER -->

    <div class="page cover">

        <h1>
            تقرير الحلقة التعليمية
        </h1>

        <h3>
            {{ $teacher->name }}
        </h3>

        <h3>
            {{ $halaqa->name }}
        </h3>

        <p>
            عدد الطلاب:
            {{ $cards['students'] }}
        </p>

        <p>
            {{ now()->format('Y-m-d') }}
        </p>

    </div>

    <!-- SUMMARY -->

    <div class="page">

        <div class="section-title">
            الملخص العام
        </div>

        <div class="cards">

            <div class="card">
                <h3>عدد الطلاب</h3>
                <p>{{ $cards['students'] }}</p>
            </div>

            <div class="card">
                <h3>الحضور</h3>
                <p>{{ $cards['attendance_rate'] }}%</p>
            </div>

            <div class="card">
                <h3>عدد التقييمات</h3>
                <p>{{ $cards['evaluations_today'] }}</p>
            </div>

            <div class="card">
                <h3>متوسط الأداء</h3>
                <p>{{ $cards['average_score'] ?? 0 }}%</p>
            </div>

        </div>

    </div>

    <!-- STUDENTS -->

    <div class="page">

        <div class="section-title">
            طلاب الحلقة
        </div>

        <table>

            <thead>
            <tr>
                <th>الطالب</th>
                <th>حضور اليوم</th>
                <th>نسبة الحضور</th>
                <th>متوسط التقييم</th>
                <th>الأداء</th>
            </tr>
            </thead>

            <tbody>

            @foreach($students as $student)

                <tr>

                    <td>
                        {{ $student['name'] }}
                    </td>

                    <td>
                        {{ $student['attendance'] }}
                    </td>

                    <td>
                        {{ $student['attendance_percentage'] }}%
                    </td>

                    <td>
                        {{ $student['average_score'] }}%
                    </td>

                    <td>
                        {{ $student['performance'] }}
                    </td>

                </tr>

            @endforeach

            </tbody>
        </table>

    </div>

    <!-- LAST EVALUATIONS -->

    <div class="page">

        <div class="section-title">
            آخر التقييمات
        </div>

        <table>

            <thead>

            <tr>

                <th>الطالب</th>

                <th>السورة</th>

                <th>من</th>

                <th>إلى</th>

                <th>الدرجة</th>

                <th>التاريخ</th>

            </tr>

            </thead>

            <tbody>

            @foreach($evaluations as $ev)

                <tr>

                    <td>
                        {{ $ev->student->first_name ?? '-' }}
                    </td>

                    <td>
                        {{ $ev->surah_name }}
                    </td>

                    <td>
                        {{ $ev->from_ayah }}
                    </td>

                    <td>
                        {{ $ev->to_ayah }}
                    </td>

                    <td>
                        {{ $ev->score }}
                    </td>

                    <td>
                        {{ $ev->evaluated_at }}
                    </td>

                </tr>

            @endforeach

            </tbody>

        </table>

    </div>

    <!-- NEED ATTENTION -->

    <div class="page">

        <div class="section-title">
            الطلاب الذين يحتاجون متابعة
        </div>

        <table>

            <thead>
            <tr>
                <th>الطالب</th>
                <th>نسبة الحضور</th>
                <th>متوسط التقييم</th>
                <th>ملاحظة</th>
            </tr>
            </thead>

            <tbody>

            @foreach($students as $student)

                @if(
                    ($student['attendance_percentage'] ?? 0) < 70
                    ||
                    ($student['average_score'] ?? 0) < 60
                )

                    <tr>

                        <td>
                            {{ $student['name'] }}
                        </td>

                        <td>
                            {{ $student['attendance_percentage'] }}%
                        </td>

                        <td>
                            {{ $student['average_score'] }}%
                        </td>

                        <td>

                            @if(
                                ($student['attendance_percentage'] ?? 0) < 70
                                &&
                                ($student['average_score'] ?? 0) < 60
                            )

                                يحتاج متابعة في الحضور والتقييم

                            @elseif(
                                ($student['attendance_percentage'] ?? 0) < 70
                            )

                                يحتاج تحسين الحضور

                            @else

                                يحتاج دعم أكاديمي

                            @endif

                        </td>

                    </tr>

                @endif

            @endforeach

            </tbody>

        </table>

        <div class="footer">
            MMS Teacher Report
        </div>

    </div>

@endif

</body>
</html>
