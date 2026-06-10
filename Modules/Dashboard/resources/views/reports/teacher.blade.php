<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'cairo', sans-serif;
            direction: rtl;
            margin: 0;
            padding: 0;
            color: #1f2937;
        }

        .page {
            padding: 10px;
            page-break-after: always;
        }

        /* تعديل غلاف التقرير ليتوافق مع أبعاد الجداول في mpdf */
        .cover-table {
            width: 100%;
            height: 100%;
            background-color: #0f172a;
            color: #ffffff;
            text-align: center;
            border: none;
        }

        .cover-table td {
            border: none;
            padding: 40px;
            vertical-align: middle;
        }

        .cover-table h1 {
            font-size: 36px;
            color: #ffffff;
            margin-bottom: 20px;
        }

        .cover-table h3 {
            font-size: 24px;
            color: #94a3b8;
            margin: 10px 0;
        }

        .cover-table p {
            font-size: 18px;
            color: #cbd5e1;
            margin-top: 20px;
        }

        .section-title {
            font-size: 22px;
            margin-top: 15px;
            margin-bottom: 15px;
            border-right: 5px solid #2563eb;
            padding-right: 10px;
            font-weight: bold;
        }

        /* استخدام الجداول كبديل آمن ومستقر للـ Flexbox لتوزيع الكروت عرضياً */
        .cards-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px;
            margin-bottom: 20px;
        }

        .cards-table td {
            width: 25%;
            border: 1px solid #e5e7eb;
            background: #f8fafc;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
        }

        .cards-table h3 {
            font-size: 13px;
            color: #64748b;
            margin: 0 0 8px 0;
        }

        .cards-table p {
            font-size: 22px;
            font-weight: bold;
            color: #1e293b;
            margin: 0;
        }

        /* الجداول العامة للبيانات */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .data-table th {
            background: #1e293b;
            color: white;
            padding: 10px;
            font-size: 14px;
            font-weight: bold;
            border: 1px solid #1e293b;
        }

        .data-table td {
            border: 1px solid #e5e7eb;
            padding: 9px;
            text-align: center;
            font-size: 13px;
        }

        .data-table tr:nth-child(even) {
            background: #f8fafc;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            color: #94a3b8;
            font-size: 11px;
            border-top: 1px dashed #e2e8f0;
            padding-top: 10px;
        }
    </style>
</head>
<body>

@if(!$has_halaqa)
    <div class="page">
        <h2 style="text-align: center; margin-top: 50px; color: #991b1b;">لا يوجد حلقة مرتبطة بهذا المعلم حالياً.</h2>
    </div>
@else

    <div class="page">
        <table class="cover-table" style="height: 900px;">
            <tr>
                <td>
                    <br><br><br>
                    <h1>تقرير الحلقة التعليمية الشامل</h1>
                    <br>
                    <h3>المعلم: {{ $teacher->name }}</h3>
                    <h3>الحلقة: {{ $halaqa->name }}</h3>
                    <br><br>
                    <p>إجمالي عدد الطلاب المقيدين: {{ $cards['students'] }} طلاب</p>
                    <br><br><br>
                    <p style="color: #64748b;">تاريخ إصدار التقرير: {{ now()->format('Y-m-d') }}</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="page">
        <div class="section-title">الملخص العام للحلقة</div>

        <table class="cards-table">
            <tr>
                <td>
                    <h3>عدد الطلاب</h3>
                    <p>{{ $cards['students'] }}</p>
                </td>
                <td>
                    <h3>نسبة الحضور</h3>
                    <p>{{ $cards['attendance_rate'] }}%</p>
                </td>
                <td>
                    <h3>تقييمات اليوم</h3>
                    <p>{{ $cards['evaluations_today'] }}</p>
                </td>
                <td>
                    <h3>متوسط الأداء</h3>
                    <p>{{ $cards['average_score'] ?? 0 }}%</p>
                </td>
            </tr>
        </table>

        <div class="section-title" style="margin-top: 30px;">بيانات طلاب الحلقة وأدائهم</div>
        <table class="data-table">
            <thead>
            <tr>
                <th>الطالب</th>
                <th>حضور اليوم</th>
                <th>نسبة الحضور للشهر</th>
                <th>متوسط التقييم</th>
                <th>مستوى الأداء</th>
            </tr>
            </thead>
            <tbody>
            @foreach($students as $student)
                <tr>
                    <td style="text-align: right; font-weight: bold;">{{ $student['name'] }}</td>
                    <td>{{ $student['attendance'] }}</td>
                    <td>{{ $student['attendance_percentage'] }}%</td>
                    <td>{{ $student['average_score'] }}%</td>
                    <td>{{ $student['performance'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="page">
        <div class="section-title">آخر التقييمات المسجلة بالحلقة</div>
        <table class="data-table">
            <thead>
            <tr>
                <th>اسم الطالب</th>
                <th>السورة</th>
                <th>من آية</th>
                <th>إلى آية</th>
                <th>الدرجة</th>
                <th>تاريخ التقييم</th>
            </tr>
            </thead>
            <tbody>
            @foreach($evaluations as $ev)
                <tr>
                    <td style="text-align: right;">{{ $ev->student->first_name ?? '-' }} {{ $ev->student->last_name ?? '' }}</td>
                    <td style="font-weight: bold; color: #1e3a8a;">{{ $ev->surah_name ?? 'غير محددة' }}</td>
                    <td>{{ $ev->from_ayah }}</td>
                    <td>{{ $ev->to_ayah }}</td>
                    <td style="font-weight: bold;">{{ $ev->score }}</td>
                    <td>{{ Carbon\Carbon::parse($ev->evaluated_at)->format('Y-m-d H:i') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="page">
        <div class="section-title">الطلاب الذين يحتاجون إلى متابعة ودعم</div>
        <table class="data-table">
            <thead>
            <tr>
                <th>اسم الطالب</th>
                <th>نسبة الحضور</th>
                <th>متوسط التقييم الاكاديمي</th>
                <th>توصية رصد المتابعة</th>
            </tr>
            </thead>
            <tbody>
            @php $hasAttentionStudents = false; @endphp
            @foreach($students as $student)
                @if(($student['attendance_percentage'] ?? 0) < 70 || ($student['average_score'] ?? 0) < 60)
                    @php $hasAttentionStudents = true; @endphp
                    <tr>
                        <td style="text-align: right; font-weight: bold;">{{ $student['name'] }}</td>
                        <td style="color: {{ $student['attendance_percentage'] < 70 ? '#b91c1c' : '#1f2937' }}">
                            {{ $student['attendance_percentage'] }}%
                        </td>
                        <td style="color: {{ $student['average_score'] < 60 ? '#b91c1c' : '#1f2937' }}">
                            {{ $student['average_score'] }}%
                        </td>
                        <td style="font-weight: dotted; color: #b45309;">
                            @if(($student['attendance_percentage'] ?? 0) < 70 && ($student['average_score'] ?? 0) < 60)
                                متابعة عاجلة (الحركية والأكاديمية متدنية)
                            @elseif(($student['attendance_percentage'] ?? 0) < 70)
                                تنبيه ضعف نسبة الحضور دون الحد المسموح
                            @else
                                توفير دعم أكاديمي وتكثيف التسميع
                            @endif
                        </td>
                    </tr>
                @endif
            @endforeach

            @if(!$hasAttentionStudents)
                <tr>
                    <td colspan="4" style="color: #15803d; font-weight: bold; padding: 20px;">
                        الحمد لله، جميع طلاب الحلقة مستوياتهم مستقرة وممتازة!
                    </td>
                </tr>
            @endif
            </tbody>
        </table>

        <div class="footer">
            نظام إدارة الحلقات التعليمية MMS • تقرير أداء المعلم الموحد
        </div>
    </div>

@endif

</body>
</html>
