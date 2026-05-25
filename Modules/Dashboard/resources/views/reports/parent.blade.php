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
            font-size:26px;
            margin-bottom:25px;
            border-right:6px solid #2563eb;
            padding-right:12px;
            color:#0f172a;
        }

        .cards{
            display:flex;
            gap:15px;
            margin-bottom:30px;
        }

        .card{
            flex:1;
            background:#f8fafc;
            border:1px solid #e5e7eb;
            border-radius:16px;
            padding:20px;
            text-align:center;
        }

        .card h3{
            margin:0;
            color:#64748b;
            font-size:15px;
        }

        .card p{
            font-size:30px;
            margin-top:15px;
            font-weight:bold;
        }

        table{
            width:100%;
            border-collapse:collapse;
            margin-top:20px;
        }

        th{
            background:#1e293b;
            color:white;
            padding:14px;
        }

        td{
            border:1px solid #e5e7eb;
            padding:12px;
            text-align:center;
        }

        tr:nth-child(even){
            background:#f8fafc;
        }

        .badge{
            display:inline-block;
            padding:5px 12px;
            border-radius:30px;
            font-size:12px;
            font-weight:bold;
        }

        .success{
            background:#dcfce7;
            color:#166534;
        }

        .danger{
            background:#fee2e2;
            color:#991b1b;
        }

        .warning{
            background:#fef3c7;
            color:#92400e;
        }

        .teacher-note{
            background:#f8fafc;
            border-right:5px solid #0ea5e9;
            padding:18px;
            border-radius:10px;
            margin-bottom:20px;
        }

        .footer{
            margin-top:40px;
            text-align:center;
            color:#94a3b8;
            font-size:12px;
        }

    </style>

</head>

<body>

@if(!$has_children)

    <div class="page">
        <h2>لا يوجد أبناء مرتبطون بهذا الحساب</h2>
    </div>

@else

    <!-- COVER -->

    <div class="page cover">

        <h1>
            تقرير متابعة الأبناء
        </h1>

        <h3>
            {{ $parent->name }}
        </h3>

        <p>
            {{ $generated_at }}
        </p>

    </div>

    <!-- SUMMARY -->

    <div class="page">

        <div class="section-title">
            الملخص العام
        </div>

        <div class="cards">

            <div class="card">
                <h3>عدد الأبناء</h3>
                <p>{{ $summary['children_count'] }}</p>
            </div>

            <div class="card">
                <h3>إجمالي الآيات</h3>
                <p>{{ $summary['total_ayahs'] }}</p>
            </div>

            <div class="card">
                <h3>متوسط الحضور</h3>
                <p>{{ $summary['average_attendance'] }}%</p>
            </div>

            <div class="card">
                <h3>متوسط التقييم</h3>
                <p>{{ $summary['average_scores'] }}%</p>
            </div>

        </div>

    </div>

    <!-- CHILDREN TABLE -->

    <div class="page">

        <div class="section-title">
            ملخص الأبناء
        </div>

        <table>

            <thead>
            <tr>
                <th>الابن</th>
                <th>الحلقة</th>
                <th>الحضور اليوم</th>
                <th>نسبة الحضور</th>
                <th>التقدم الشهري</th>
                <th>متوسط التقييم</th>
                <th>الأداء</th>
            </tr>
            </thead>

            <tbody>

            @foreach($children as $child)

                <tr>

                    <td>{{ $child['name'] }}</td>

                    <td>{{ $child['halaqa'] }}</td>

                    <td>

                        @if($child['attendance'] == 'present')

                            <span class="badge success">
                            حاضر
                        </span>

                        @elseif($child['attendance'] == 'absent')

                            <span class="badge danger">
                            غائب
                        </span>

                        @else

                            <span class="badge warning">
                            لم يرصد
                        </span>

                        @endif

                    </td>

                    <td>
                        {{ $child['attendance_percentage'] }}%
                    </td>

                    <td>
                        {{ $child['month_progress'] }} آية
                    </td>

                    <td>
                        {{ $child['average_score'] }}%
                    </td>

                    <td>
                        {{ $child['performance_level'] }}
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
                <th>الابن</th>
                <th>السورة</th>
                <th>من</th>
                <th>إلى</th>
                <th>الدرجة</th>
            </tr>
            </thead>

            <tbody>

            @foreach($children as $child)

                @if($child['last_evaluation'])

                    <tr>

                        <td>{{ $child['name'] }}</td>

                        <td>
                            {{ $child['last_evaluation']->surah_name ?? '-' }}
                        </td>

                        <td>
                            {{ $child['last_evaluation']->from_ayah ?? '-' }}
                        </td>

                        <td>
                            {{ $child['last_evaluation']->to_ayah ?? '-' }}
                        </td>

                        <td>
                            {{ $child['last_evaluation']->score ?? '-' }}
                        </td>

                    </tr>

                @endif

            @endforeach

            </tbody>

        </table>

        <div class="footer">
            MMS Parent Report
        </div>

    </div>


@endif


</body>
</html>
