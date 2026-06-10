<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
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

        /* تصميم صفحة الغلاف الإشرافية */
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
            font-size: 32px;
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

        /* جدول بيانات الغلاف */
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
            font-size: 20px;
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

        /* مكونات الصفحات الداخلية */
        .section-title {
            font-size: 20px;
            margin-top: 10px;
            margin-bottom: 20px;
            border-right: 6px solid #2563eb;
            padding-right: 12px;
            font-weight: bold;
            color: #0f172a;
        }

        /* كروت الإحصائيات الشاملة للمسجد */
        .cards-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px;
            margin-bottom: 30px;
        }

        .cards-table td {
            width: 25%;
            border: 1px solid #f1f5f9;
            background: #f8fafc;
            border-radius: 16px;
            padding: 18px 10px;
            text-align: center;
        }

        .cards-table h3 {
            font-size: 12px;
            color: #64748b;
            margin: 0 0 10px 0;
            font-weight: 600;
        }

        .cards-table p {
            font-size: 22px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
        }

        /* جداول البيانات الأساسية */
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

        /* شارات التقييم والحالة */
        .badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }

        .badge-present { background-color: #dcfce7; color: #166534; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-absent { background-color: #fee2e2; color: #991b1b; }

        /* هيكلة الجداول المزدوجة المتجاورة لتعمل بسلام مع mPDF */
        .split-container-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .split-cell {
            width: 48%;
            vertical-align: top;
            border: none !important;
            padding: 0 !important;
        }

        .split-spacer {
            width: 4%;
            border: none !important;
            padding: 0 !important;
        }

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
                    <h1>{{ $title }}</h1>
                </td>
            </tr>
            <tr>
                <td class="row-subtitle">
                    <div class="cover-subtitle">المؤشرات الإشرافية الشاملة وعمليات التدخل الرقابي على أداء الحلقات القرآنية</div>
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
                                المشرف العام: {{ $supervisor->name ?? 'مشرف المسجد' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="info-cell-sub">
                                نطاق المتابعة: تقرير أداء المسجد والجمعية الموحد
                            </td>
                        </tr>
                        <tr>
                            <td class="info-cell-date">
                                تاريخ صدور التقرير: {{ $date }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="row-footer">
                    <div class="cover-footer">
                        تَقْرِيرٌ رَقَابِيٌّ صَادِرٌ عَنْ مَنَصَّةِ وَصْل التعليمية
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="page page-break">
    <div class="section-title">الملخص العام للمسجد والحلقات القائمة</div>

    <table class="cards-table">
        <tr>
            <td>
                <h3>إجمالي الحلقات</h3>
                <p style="color: #2563eb;">{{ $stats['total_halaqats'] }} حلقات</p>
            </td>
            <td>
                <h3>الطلاب المسجلين</h3>
                <p style="color: #0f172a;">{{ $stats['total_students'] }} طلاب</p>
            </td>
            <td>
                <h3>معدل الحضور العام</h3>
                <p style="color: #16a34a;">{{ $stats['attendance_rate'] }}%</p>
            </td>
            <td>
                <h3>درجات التحصيل العام</h3>
                <p style="color: #ea580c;">{{ $stats['average_score'] }}%</p>
            </td>
        </tr>
    </table>

    <div class="section-title" style="margin-top: 35px;">📊 الرقابة التفصيلية على أداء الحلقات</div>
    <table class="data-table">
        <thead>
        <tr>
            <th>كود الحلقة</th>
            <th style="text-align: right; width: 30%;">اسم الحلقة التعليمية</th>
            <th>المعلم المشرف</th>
            <th>عدد الطلاب</th>
            <th>انضباط حضور الشهر</th>
            <th>متوسط التقييم العام</th>
        </tr>
        </thead>
        <tbody>
        @foreach($halaqat as $halaqa)
            <tr>
                <td>#{{ $halaqa['id'] }}</td>
                <td style="text-align: right; font-weight: bold; color: #0f172a;">{{ $halaqa['name'] }}</td>
                <td>{{ $halaqa['teacher'] }}</td>
                <td>{{ $halaqa['students_count'] }} طلاب</td>
                <td>
                            <span class="badge {{ $halaqa['attendance_rate'] >= 80 ? 'badge-present' : 'badge-warning' }}">
                                {{ $halaqa['attendance_rate'] }}%
                            </span>
                </td>
                <td style="font-weight: bold; color: #2563eb;">{{ $halaqa['average_score'] }}%</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="page page-break">

    <table class="split-container-table">
        <tr>
            <td class="split-cell">
                <div class="section-title">🏆 المعلمون الأعلى إنتاجية وتحصيلاً</div>
                <table class="data-table">
                    <thead>
                    <tr>
                        <th style="text-align: right; width: 60%;">اسم المعلم</th>
                        <th>معدل إتقان الحلقة</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($top_teachers as $teacher)
                        <tr>
                            <td style="text-align: right; font-weight: bold; color: #0f172a;">{{ $teacher['name'] }}</td>
                            <td><span class="badge badge-present">{{ $teacher['score'] }}% إتقان</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </td>

            <td class="split-spacer"></td>

            <td class="split-cell">
                <div class="section-title">⚠️ طلاب بحاجة إلى تدخل أكاديمي</div>
                <table class="data-table">
                    <thead>
                    <tr>
                        <th style="text-align: right; width: 60%;">اسم الطالب المتعثر</th>
                        <th>معدل الدرجات</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($weak_students as $student)
                        <tr>
                            <td style="text-align: right; font-weight: bold;">{{ $student['name'] }}</td>
                            <td><span class="badge badge-absent">{{ $student['score'] }}% تعثر</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" style="color: #16a34a; font-weight: bold; padding: 15px;">لا يوجد طلاب متعثرين حالياً ✨</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <div class="section-title" style="margin-top: 40px;">🚨 الطلاب الأكثر غياباً وانقطاعاً خلال الشهر (رعاية ومتابعة)</div>
    <table class="data-table">
        <thead>
        <tr>
            <th style="text-align: right; width: 35%;">اسم الطالب المنقطع</th>
            <th style="width: 25%;">أيام الغياب المرصودة</th>
            <th style="text-align: right; width: 40%;">الإجراء الرقابي المقترح من المشرف</th>
        </tr>
        </thead>
        <tbody>
        @forelse($absentees as $abs)
            <tr>
                <td style="text-align: right; font-weight: bold; color: #0f172a;">{{ $abs['student_name'] }}</td>
                <td style="color: #dc2626; font-weight: bold;">{{ $abs['absent_days_this_month'] }} أيام غياب</td>
                <td style="font-size: 11px; color: #b45309; text-align: right;">📞 يتطلب تفعيل الاتصال الفوري بولي الأمر وإحالة السجل لمدير المسجد.</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" style="color: #16a34a; font-weight: bold; padding: 15px;">انضباط تام: لا توجد حالات غياب متكررة تختت الحدود المسموح بها.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div style="margin-top: 50px; padding: 22px; background-color: #f8fafc; border: 1px dashed #e2e8f0; border-radius: 12px; text-align: center;">
        <p style="margin: 0; font-size: 14px; color: #475569; line-height: 1.6; font-weight: 500;">
            "إن هذا العلم دين فانتظروا عمن تأخذون دينكم" .. يعكس هذا التقرير الرقابي الشامل الحالة التشغيلية والتربوية الفورية لحلقات المسجد المعتمدة في منصة وصل.
        </p>
    </div>

    <div class="footer">
        منصة وَصْل التعليمية لإدارة الحلقات القرآنية • تقرير إشراف المسجد الدوري الموحد
    </div>
</div>

</body>
</html>
