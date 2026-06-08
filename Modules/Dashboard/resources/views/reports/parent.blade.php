<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <style>
        /* إعدادات الصفحة والخطوط الأساسية */
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

        /* 🕌 هيكل الغلاف المطور بنظام الجداول المعزولة */
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

        /* توزيع الأسطر بدقة عبر خلايا الجدول */
        .row-logo { padding-top: 50px; padding-bottom: 20px; text-align: center; }
        .row-basmala { padding-bottom: 35px; text-align: center; }
        .row-title { padding-bottom: 15px; text-align: center; }
        .row-subtitle { padding-bottom: 35px; text-align: center; }
        .row-divider { padding-bottom: 35px; text-align: center; }
        .row-info { padding-bottom: 40px; text-align: center; }
        .row-footer { padding-bottom: 40px; text-align: center; }

        /* 🖼️ تنسيق اللوجو */
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
            font-size: 36px;
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

        /* 🎯 جدول بيانات ولي الأمر المحدث لتفادي الالتصاق */
        .info-table {
            width: 85%;
            margin: 0 auto;
            border-collapse: collapse;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
        }

        /* 🛠️ الحل: تم توزيع الأسطر على خلايا مستقلة عمودياً مع padding إجباري */
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
            font-size: 15px;
            color: #475569;
            font-weight: 500;
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

        /* 📊 تنسيقات الصفحات الداخلية */
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
            width: 25%;
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
            font-size: 26px;
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
        .badge-none { background-color: #fef3c7; color: #92400e; }

        .perf-excelent { background-color: #e0f2fe; color: #0369a1; }
        .perf-good { background-color: #f0fdf4; color: #166534; }
        .perf-need-work { background-color: #fff7ed; color: #c2410c; }

        .teacher-notes-text {
            text-align: right !important;
            font-size: 12px !important;
            color: #475569;
            line-height: 1.5;
            font-style: italic;
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

@if(!$has_children)
    <div class="page">
        <div style="text-align: center; margin-top: 100px; padding: 30px; border: 1px dashed #fee2e2; background-color: #fff5f5; border-radius: 16px;">
            <h2 style="color: #991b1b; margin: 0; font-size: 20px;">لا يوجد أبناء مرتبطون بهذا الحساب حالياً.</h2>
        </div>
    </div>
@else

    <div class="page">
        <div class="cover-container">
            <table class="cover-table">
                <tr>
                    <td class="row-logo">
                        <img src=" https://koihzqfwzvnrcrrtpnyg.supabase.co/storage/v1/object/public/images/logo.png" class="app-logo" alt="وصل">
                    </td>
                </tr>
                <tr>
                    <td class="row-basmala">
                        <div class="cover-basmala">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</div>
                    </td>
                </tr>
                <tr>
                    <td class="row-title">
                        <h1>تَقْرِيرُ مُتَابَعَةِ الأَبْنَاءِ</h1>
                    </td>
                </tr>
                <tr>
                    <td class="row-subtitle">
                        <div class="cover-subtitle">الدورة التحصيليّة لحلقات تحفيظ القرآن الكريم</div>
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
                                     ولي الأمر المحترم: {{ $parent->name }}
                                </td>
                            </tr>
                            <tr>
                                <td class="info-cell-sub">
                                    تطبيق وَصْل لمتابعة الحلقات التعليمية
                                </td>
                            </tr>
                            <tr>
                                <td class="info-cell-date">
                                    تاريخ التوليد الفعلي: {{ $generated_at }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="row-footer">
                        <div class="cover-footer">
                                تَقْرِيرٌ رَسْمِيٌّ صَادِرٌ عَبْرَ تَطْبِيقِ وَصْل
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="page page-break">
        <div class="section-title">الملخص العام للملف</div>

        <table class="cards-table">
            <tr>
                <td>
                    <h3>عدد الأبناء المتابعين</h3>
                    <p>{{ $summary['children_count'] }}</p>
                </td>
                <td>
                    <h3>إجمالي الآيات المنجزة</h3>
                    <p style="color: #2563eb;">{{ $summary['total_ayahs'] }}</p>
                </td>
                <td>
                    <h3>متوسط حضور الأبناء</h3>
                    <p style="color: #16a34a;">{{ $summary['average_attendance'] }}%</p>
                </td>
                <td>
                    <h3>متوسط التحصيل العام</h3>
                    <p style="color: #ea580c;">{{ $summary['average_scores'] }}%</p>
                </td>
            </tr>
        </table>

        <div class="section-title" style="margin-top: 35px;">ملخص الأداء التحصيلي والانتظام</div>
        <table class="data-table">
            <thead>
            <tr>
                <th style="text-align: right; width: 20%;">الابن</th>
                <th>الحلقة التعليمية</th>
                <th>حضور اليوم</th>
                <th>نسبة حضور الشهر</th>
                <th>إنجاز الشهر الحالي</th>
                <th>متوسط التقييم</th>
                <th>مستوى الأداء العام</th>
            </tr>
            </thead>
            <tbody>
            @foreach($children as $child)
                <tr>
                    <td style="text-align: right; font-weight: bold; color: #0f172a;">{{ $child['name'] }}</td>
                    <td>{{ $child['halaqa'] }}</td>
                    <td>
                        @if($child['attendance'] == 'present')
                            <span class="badge badge-present">حاضر</span>
                        @elseif($child['attendance'] == 'absent')
                            <span class="badge badge-absent">غائب</span>
                        @else
                            <span class="badge badge-none">لم يرصد</span>
                        @endif
                    </td>
                    <td style="font-weight: 600;">{{ $child['attendance_percentage'] }}%</td>
                    <td style="font-weight: bold; color: #2563eb;">{{ $child['month_progress'] }} آية</td>
                    <td style="font-weight: 600;">{{ $child['average_score'] }}%</td>
                    <td>
                        @if($child['average_score'] >= 90)
                            <span class="badge perf-excelent">{{ $child['performance_level'] }}</span>
                        @elseif($child['average_score'] >= 60)
                            <span class="badge perf-good">{{ $child['performance_level'] }}</span>
                        @else
                            <span class="badge perf-need-work">{{ $child['performance_level'] }}</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="page page-break">
        <div class="section-title">تفاصيل آخر جلسة تسميع وتقييم لكل ابن</div>
        <table class="data-table">
            <thead>
            <tr>
                <th style="text-align: right; width: 18%;">اسم الابن</th>
                <th style="width: 15%;">السورة الكريمة</th>
                <th style="width: 8%;">من آية</th>
                <th style="width: 8%;">إلى آية</th>
                <th style="width: 11%;">الدرجة</th>
                <th style="text-align: right; width: 40%;">توجيهات وملاحظات المعلم</th>
            </tr>
            </thead>
            <tbody>
            @php $hasEvaluations = false; @endphp
            @foreach($children as $child)
                @if($child['last_evaluation'])
                    @php $hasEvaluations = true; @endphp
                    <tr>
                        <td style="text-align: right; font-weight: bold; color: #0f172a;">{{ $child['name'] }}</td>
                        <td style="font-weight: bold; color: #16a34a;">سورة {{ $child['last_evaluation']->surah_name ?? '-' }}</td>
                        <td>{{ $child['last_evaluation']->from_ayah ?? '-' }}</td>
                        <td>{{ $child['last_evaluation']->to_ayah ?? '-' }}</td>
                        <td style="font-weight: bold; color: #ea580c; font-size: 14px;">{{ $child['last_evaluation']->score ?? '-' }}%</td>

                        <td class="teacher-notes-text">
                            {{ $child['last_evaluation']->notes ?? 'لا توجد ملاحظات مسجلة لهذه الجلسة.' }}
                        </td>
                    </tr>
                @endif
            @endforeach

            @if(!$hasEvaluations)
                <tr>
                    <td colspan="6" style="color: #94a3b8; padding: 30px; font-style: italic;">لا توجد جلسات تسميع مسجلة مؤخراً للأبناء.</td>
                </tr>
            @endif
            </tbody>
        </table>

        <div style="margin-top: 60px; padding: 20px; background-color: #f8fafc; border: 1px dashed #e2e8f0; border-radius: 12px; text-align: center;">
            <p style="margin: 0; font-size: 14px; color: #475569; line-height: 1.6; font-weight: 500;">
                "خيركم من تعلم القرآن وعلمه" .. نشكر لكم حرصكم ومتابعتكم المستمرة لأبنائكم في رحلتهم المباركة مع كتاب الله تعالى.
            </p>
        </div>

        <div class="footer">
            تطبيق وَصْل لمتابعة الحلقات التعليمية • تقرير المتابعة الدوري لأولياء الأمور
        </div>
    </div>

@endif

</body>
</html>
