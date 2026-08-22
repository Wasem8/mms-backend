<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>

    <style>
        @font-face {
            font-family: 'Cairo';
            font-style: normal;
            font-weight: normal;
            src: url('/tmp/Cairo-Regular.ttf') format('truetype');
        }

        @font-face {
            font-family: 'Cairo';
            font-style: normal;
            font-weight: bold;
            src: url('/tmp/Cairo-Bold.ttf') format('truetype');
        }

        body {
            font-family: 'Cairo', sans-serif;
            direction: rtl;
            margin: 0;
            padding: 0;
            unicode-bidi: embed;
            color: #334155;
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
            font-family: 'Cairo', sans-serif;
        }

        .cover-title {
            font-size: 32px;
            font-weight: bold;
            color: #0f172a;
            font-family: 'Cairo', sans-serif;
        }

        .cover-subtitle {
            color: #64748b;
            font-size: 15px;
            font-family: 'Cairo', sans-serif;
        }

        .info-table {
            width: 80%;
            margin: auto;
            border-collapse: collapse;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .info-table td {
            padding: 14px;
            text-align: center;
            font-family: 'Cairo', sans-serif;
        }

        .section-title {
            font-size: 20px;
            font-weight: bold;
            border-right: 6px solid #2563eb;
            padding-right: 10px;
            margin-bottom: 20px;
            color: #0f172a;
            font-family: 'Cairo', sans-serif;
        }

        .cards-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px;
            margin-bottom: 25px;
        }

        .cards-table td {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 18px;
            text-align: center;
            border-radius: 12px;
        }

        .cards-table h3 {
            margin: 0 0 10px;
            font-size: 12px;
            color: #64748b;
            font-family: 'Cairo', sans-serif;
        }

        .cards-table p {
            margin: 0;
            font-size: 22px;
            font-weight: bold;
            font-family: 'Cairo', sans-serif;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            background: #1e293b;
            color: #fff;
            padding: 12px;
            font-size: 13px;
            font-family: 'Cairo', sans-serif;
            font-weight: bold;
        }

        .data-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            text-align: center;
            font-family: 'Cairo', sans-serif;
        }

        .data-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: bold;
            font-family: 'Cairo', sans-serif;
        }

        .badge-success {
            background: #dcfce7;
            color: #166534;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            color: #94a3b8;
            font-size: 11px;
            font-family: 'Cairo', sans-serif;
        }
    </style>

</head>

<body>

<!-- COVER -->
<div class="page">
    <div class="cover-container">
        <table class="cover-table">
            <tr>
                <td class="row-logo">
                    <img src="https://koihzqfwzvnrcrrtpnyg.supabase.co/storage/v1/object/public/images/logo.png"
                         class="app-logo">
                </td>
            </tr>

            <tr>
                <td class="row-basmala">
                    <div class="cover-basmala">
                        بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ
                    </div>
                </td>
            </tr>

            <tr>
                <td class="row-title">
                    <div class="cover-title">
                        {{ $title }}
                    </div>
                </td>
            </tr>

            <tr>
                <td class="row-subtitle">
                    <div class="cover-subtitle">
                        تقرير رقابي مختصر لأداء الحلقات القرآنية
                    </div>
                </td>
            </tr>

            <tr>
                <td class="row-info">
                    <table class="info-table">
                        <tr>
                            <td>
                                مُعد التقرير: {{ $user_name }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                تاريخ التقرير: {{ $date }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

        </table>

    </div>
</div>

<!-- MAIN PAGE -->
<div class="page page-break">

    <div class="section-title">
        الملخص التنفيذي
    </div>

    <table class="cards-table">
        <tr>
            <td>
                <h3>الحلقات</h3>
                <p>{{ $stats['total_halaqats'] }}</p>
            </td>

            <td>
                <h3>الطلاب</h3>
                <p>{{ $stats['total_students'] }}</p>
            </td>

            <td>
                <h3>الحضور</h3>
                <p>{{ $stats['attendance_rate'] }}%</p>
            </td>

            <td>
                <h3>متوسط الأداء</h3>
                <p>{{ $stats['average_score'] }}%</p>
            </td>

        </tr>

    </table>

    <div class="section-title">
        أداء الحلقات
    </div>

    <table class="data-table">

        <thead>
        <tr>
            <th>#</th>
            <th>الحلقة</th>
            <th>المعلم</th>
            <th>الطلاب</th>
            <th>الحضور</th>
            <th>التقييم</th>
        </tr>
        </thead>

        <tbody>

        @foreach($halaqat as $halaqa)

            <tr>

                <td>{{ $halaqa['id'] }}</td>

                <td style="text-align:right">
                    {{ $halaqa['name'] }}
                </td>

                <td>
                    {{ $halaqa['teacher'] }}
                </td>

                <td>
                    {{ $halaqa['students_count'] }}
                </td>

                <td>

                <span class="badge {{ $halaqa['attendance_rate'] >= 80 ? 'badge-success' : 'badge-warning' }}">
                    {{ $halaqa['attendance_rate'] }}%
                </span>

                </td>

                <td>
                    {{ $halaqa['average_score'] }}%
                </td>

            </tr>

        @endforeach

        </tbody>

    </table>

    <br><br>

    <div class="footer">
        منصة وَصْل التعليمية • التقرير الرقابي المختصر
    </div>

</div>

</body>
</html>
