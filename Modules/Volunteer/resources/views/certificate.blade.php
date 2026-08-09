<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'cairo', sans-serif;
            direction: rtl;
            margin: 0;
            padding: 0;
            color: #1e293b;
            background: #ffffff;
            width: 297mm;
            height: 210mm;
        }

        .certificate-wrapper {
            width: 100%;
            height: 100%;
            padding: 18mm 20mm;
        }

        .certificate-border {
            border: 3px solid #15803d;
            border-radius: 6px;
            padding: 12mm 16mm;
            height: 100%;
            position: relative;
            background: #f8fdf9;
        }

        .certificate-border::before {
            content: "";
            position: absolute;
            top: 8px;
            left: 8px;
            right: 8px;
            bottom: 8px;
            border: 1px solid #86efac;
            border-radius: 3px;
            pointer-events: none;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .basmala {
            font-size: 16px;
            color: #166534;
            font-weight: 600;
            margin: 0 0 14px 0;
            letter-spacing: 0.5px;
        }

        .title {
            font-size: 40px;
            color: #15803d;
            font-weight: 800;
            margin: 0 0 6px 0;
            letter-spacing: 1px;
        }

        .subtitle {
            font-size: 14px;
            color: #64748b;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .divider {
            width: 120px;
            height: 3px;
            background: linear-gradient(90deg, transparent, #22c55e, transparent);
            margin: 22px auto;
        }

        .content {
            text-align: center;
            margin: 28px 0;
        }

        .content p {
            font-size: 16px;
            line-height: 1.9;
            margin: 6px 0;
            color: #475569;
        }

        .content .volunteer-name {
            font-size: 30px;
            color: #14532d;
            font-weight: 800;
            margin: 14px 0;
            padding-bottom: 6px;
            border-bottom: 2px solid #bbf7d0;
            display: inline-block;
        }

        .content .opportunity-title {
            font-size: 19px;
            color: #0f172a;
            font-weight: 700;
        }

        .details-table {
            width: 55%;
            margin: 26px auto 10px;
            border-collapse: collapse;
        }

        .details-table td {
            padding: 8px 18px;
            font-size: 14px;
            border-bottom: 1px solid #dcfce7;
        }

        .details-table td:first-child {
            font-weight: 700;
            color: #166534;
            text-align: left;
        }

        .details-table td:last-child {
            color: #0f172a;
            text-align: right;
        }

        .footer {
            position: absolute;
            bottom: 14mm;
            left: 16mm;
            right: 16mm;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            border-top: 1px solid #dcfce7;
            padding-top: 10px;
        }

        .stamp {
            text-align: center;
            margin-top: 22px;
        }

        .stamp-circle {
            display: inline-block;
            border: 2.5px solid #15803d;
            border-radius: 50%;
            width: 84px;
            height: 84px;
            line-height: 80px;
            text-align: center;
            color: #15803d;
            font-weight: 700;
            font-size: 14px;
            opacity: 0.85;
        }
    </style>
</head>

<body>
    <div class="certificate-wrapper">
        <div class="certificate-border">
            <div class="header">
                <p class="basmala">بِسْمِ اللَّهِ الرَّحْمَـٰنِ الرَّحِيمِ</p>
                <h1 class="title">شهادة شكر وتقدير</h1>
                <p class="subtitle">نظام إدارة المساجد</p>
            </div>

            <div class="divider"></div>

            <div class="content">
                <p>يشرفنا أن نقدم هذه الشهادة تقديراً لجهود</p>
                <p class="volunteer-name">{{ $volunteerName }}</p>
                <p>لمشاركته الفاعلة في</p>
                <p class="opportunity-title">{{ $opportunityTitle }}</p>
                <p>بمسجد</p>
                <p class="opportunity-title" style="font-size:16px;">{{ $mosqueName }}</p>
            </div>

            <table class="details-table">
                <tr>
                    <td>إجمالي ساعات التطوع</td>
                    <td>{{ $totalHours }} ساعة</td>
                </tr>
                <tr>
                    <td>تاريخ الإصدار</td>
                    <td>{{ $issuedAt }}</td>
                </tr>
            </table>

            <div class="stamp">
                <div class="stamp-circle">معتمد</div>
            </div>

            <div class="footer">
                تم إصدار هذه الشهادة إلكترونياً عبر نظام إدارة المساجد
            </div>
        </div>
    </div>
</body>

</html>
