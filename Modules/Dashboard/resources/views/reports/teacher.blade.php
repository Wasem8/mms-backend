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
            color: #334155;
            background-color: #ffffff;
        }

        .page {
            padding: 20px;
        }

        .page-break {
            page-break-before: always;
        }

        .cover-container {
            width: 100%;
            height: 940px;
            border: 2px solid #e2e8f0;
            border-radius: 24px;
            background-color: #ffffff;
        }

        .cover-table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
        }

        .row-logo { padding-top: 50px; padding-bottom: 20px; text-align: center; }
        .row-basmala { padding-bottom: 35px; text-align: center; }
        .row-title { padding-bottom: 15px; text-align: center; }
        .row-subtitle { padding-bottom: 35px; text-align: center; }
        .row-divider { padding-bottom: 35px; text-align: center; }
        .row-info { padding-bottom: 40px; text-align: center; }
        .row-footer { padding-bottom: 40px; text-align: center; }

        .app-logo {
            width: 130px;
            height: auto;
            display: inline-block;
        }

        .cover-basmala {
            font-size: 19px;
            color: #2563eb;
            font-weight: bold;
            margin: 0;
        }

        .cover-table h1 {
            font-size: 34px;
            color: #0f172a;
            margin: 0;
            font-weight: bold;
            line-height: 1.4;
        }

        .cover-subtitle {
            font-size: 16px;
            color: #64748b;
            margin: 0;
            font-weight: 500;
        }

        .islamic-divider {
            font-size: 20px;
            color: #d97706;
            margin: 0;
            letter-spacing: 6px;
        }

        .info-table {
            width: 85%;
            margin: 0 auto;
            border-collapse: collapse;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
        }

        .info-cell-title {
            padding: 22px 20px 8px 20px !important;
            text-align: center !important;
            font-size: 22px;
            color: #0f172a;
            font-weight: bold;
            border: none !important;
        }

        .info-cell-sub {
            padding: 8px 20px 8px 20px !important;
            text-align: center !important;
            font-size: 16px;
            color: #2563eb;
            font-weight: bold;
            border: none !important;
        }

        .info-cell-date {
            padding: 8px 20px 22px 20px !important;
            text-align: center !important;
            font-size: 13px;
            color: #64748b;
            border: none !important;
        }

        .cover-footer {
            font-size: 14px;
            color: #64748b;
            font-weight: bold;
            margin: 0;
        }

        .section-title {
            font-size: 20px;
            margin-top: 10px;
            margin-bottom: 20px;
            border-right: 6px solid #2563eb;
            padding-right: 12px;
            font-weight: bold;
            color: #0f172a;
        }

        .cards-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px;
            margin-bottom: 30px;
        }

        .cards-table td {
            width: 33.33%;
            border: 1px solid #f1f5f9;
            background: #f8fafc;
            border-radius: 16px;
            padding: 20px 15px;
            text-align: center;
        }

        .cards-table h3 {
            font-size: 12px;
            color: #64748b;
            margin: 0 0 10px 0;
            font-weight: 600;
        }

        .cards-table p {
            font-size: 24px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            border-radius: 12px;
            overflow: hidden;
        }

        .data-table th {
            background: #1e293b;
            color: #f8fafc;
            padding: 14px 10px;
            font-size: 13px;
            font-weight: bold;
            text-align: center;
            border-bottom: 3px solid #cbd5e1;
        }

        .data-table td {
            border-bottom: 1px solid #f1f5f9;
            padding: 12px 10px;
            text-align: center;
            font-size: 13px;
            color: #334155;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }

        .badge-present { background-color: #dcfce7; color: #166534; }
        .badge-absent { background-color: #fee2e2; color: #991b1b; }

        .footer {
            text-align: center;
            margin-top: 50px;
            color: #94a3b8;
            font-size: 11px;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
        }
    </style>
</head>

<body>

@if(!$has_halaqa)
    <div class="page">
        <div style="text-align: center; margin-top: 100px; padding: 30px; border: 1px dashed #fee2e2; background-color: #fff5f5; border-radius: 16px;">
            <h2 style="color: #991b1b; margin: 0; font-size: 20px;">لا يوجد حلقة مرتبطة بهذا المعلم حالياً.</h2>
        </div>
    </div>
@else

    <div class="page">
        <div class="cover-container">
            <table class="cover-table">
                <tr>
                    <td class="row-logo">
                        <img src="https://koihzqfwzvnrcrrtpnyg.supabase.co/storage/v1/object/public/images/logo.png" class="app-logo" alt="وصل">
                    </td>
                </tr>
                <tr>
                    <td class="row-basmala">
                        <div class="cover-basmala">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</div>
                    </td>
                </tr>
                <tr>
                    <td class="row-title">
                        <h1>تَقْرِيرُ أَدَاءِ الحَلَقَةِ التَّعْلِيمِيَّةِ</h1>
                    </td>
                </tr>
                <tr>
                    <td class="row-subtitle">
                        <div class="cover-subtitle">سجل رصد الحضور الأكاديمي والتقييمات المباشرة للطلاب</div>
                    </td>
                </tr>
                <tr>
                    <td class="row-divider">
                        <div class="islamic-divider">❖  ❖  ❖</div>
                    </td>
                </tr>
                <tr>
                    <td class="row-info">
                        <table class="info-table">
                            <tr>
                                <td class="info-cell-title">
                                    المعلم: {{ $teacher->name }}
                                </td>
                            </tr>
                            <tr>
                                <td class="info-cell-sub">
                                    الحلقة التعليمية: {{ $halaqa->name }}
                                </td>
                            </tr>
                            <tr>
                                <td class="info-cell-date">
                                    تاريخ صدور التقرير: {{ now()->format('Y-m-d') }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="row-footer">
                        <div class="cover-footer">
                            تَقْرِيرٌ صَادِرٌ عَنْ مَنَصَّةِ وَصْل التعليمية
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="page page-break">
        <div class="section-title">الملخص العام للحلقة</div>

        <table class="cards-table">
            <tr>
                <td>
                    <h3>إجمالي الطلاب المسجلين</h3>
                    <p style="color: #0f172a;">{{ $cards['students'] }} طلاب</p>
                </td>
                <td>
                    <h3>نسبة الحضور العامة</h3>
                    <p style="color: #16a34a;">{{ $cards['attendance_rate'] }}%</p>
                </td>
                <td>
                    <h3>متوسط درجات التحصيل العام</h3>
                    <p style="color: #ea580c;">{{ $cards['average_score'] ?? 0 }}%</p>
                </td>
            </tr>
        </table>

        <div class="section-title" style="margin-top: 35px;">سجل الحضور والتقييم الأكاديمي للطلاب</div>
        <table class="data-table">
            <thead>
            <tr>
                <th style="text-align: right; width: 40%;">اسم الطالب</th>
                <th style="width: 30%;">نسبة الحضور بالشهر</th>
                <th style="width: 30%;">متوسط درجات التسميع</th>
            </tr>
            </thead>
            <tbody>
            @foreach($students as $student)
                <tr>
                    <td style="text-align: right; font-weight: bold; color: #0f172a;">{{ $student['name'] }}</td>
                    <td style="font-weight: 600;">
                        <span class="badge {{ $student['attendance_percentage'] >= 75 ? 'badge-present' : 'badge-absent' }}">
                            {{ $student['attendance_percentage'] }}%
                        </span>
                    </td>
                    <td style="font-weight: bold; color: #2563eb;">{{ $student['average_score'] }}%</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="page page-break">
        <div class="section-title">سجل التقييمات التفصيلي لعمليات التسميع</div>
        <table class="data-table">
            <thead>
            <tr>
                <th style="text-align: right; width: 30%;">اسم الطالب</th>
                <th>السورة</th>
                <th>نطاق الآيات</th>
                <th>الدرجة</th>
            </tr>
            </thead>
            <tbody>
            @foreach($evaluations as $ev)
                <tr>
                    <td style="text-align: right; font-weight: bold; color: #0f172a;">
                        {{ $ev->student->first_name ?? '-' }} {{ $ev->student->last_name ?? '' }}
                    </td>
                    <td style="font-weight: bold; color: #16a34a;">سورة {{ $ev->surah_name ?? 'غير محددة' }}</td>
                    <td style="color: #475569; font-weight: 600;">من {{ $ev->from_ayah }} إلى {{ $ev->to_ayah }}</td>
                    <td style="font-weight: bold; color: #ea580c;">{{ $ev->score }}%</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div style="margin-top: 80px; padding: 25px; background-color: #f8fafc; border: 1px dashed #e2e8f0; border-radius: 12px; text-align: center;">
            <p style="margin: 0; font-size: 14px; color: #475569; line-height: 1.6; font-weight: 500;">
                "خيركم من تعلم القرآن وعلمه" .. يعكس هذا التقرير الأداء الفعلي واليومي للرصد الصادر من المعلم المشرف على الحلقة.
            </p>
        </div>

        <div class="footer">
            منصة وَصْل التعليمية لإدارة الحلقات • تقرير رصد المعلم الدوري الموحد
        </div>
    </div>

@endif

</body>
</html>
