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
            background: #ffffff;
        }

        .certificate-wrapper {
            width: 100%;
            height: 100%;
            padding: 40px;
            box-sizing: border-box;
        }

        .certificate-border {
            border: 4px solid #d97706;
            border-radius: 24px;
            padding: 50px 40px;
            background: #fffbeb;
            min-height: 700px;
            position: relative;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .basmala {
            font-size: 22px;
            color: #2563eb;
            font-weight: bold;
            margin: 0 0 10px 0;
        }

        .title {
            font-size: 36px;
            color: #d97706;
            font-weight: bold;
            margin: 0 0 5px 0;
        }

        .subtitle {
            font-size: 18px;
            color: #64748b;
            margin: 0 0 30px 0;
        }

        .divider {
            text-align: center;
            font-size: 24px;
            color: #d97706;
            letter-spacing: 6px;
            margin: 20px 0;
        }

        .content {
            text-align: center;
            margin: 40px 0;
        }

        .content p {
            font-size: 20px;
            line-height: 2;
            margin: 15px 0;
            color: #334155;
        }

        .content .highlight {
            color: #d97706;
            font-weight: bold;
            font-size: 24px;
        }

        .content .volunteer-name {
            font-size: 32px;
            color: #2563eb;
            font-weight: bold;
            margin: 20px 0;
        }

        .content .opportunity-title {
            font-size: 22px;
            color: #0f172a;
            font-weight: bold;
        }

        .details-table {
            width: 60%;
            margin: 30px auto;
            border-collapse: collapse;
        }

        .details-table td {
            padding: 10px 20px;
            font-size: 18px;
            border-bottom: 1px solid #e2e8f0;
        }

        .details-table td:first-child {
            font-weight: bold;
            color: #64748b;
            text-align: left;
        }

        .details-table td:last-child {
            color: #0f172a;
            text-align: right;
        }

        .footer {
            position: absolute;
            bottom: 30px;
            left: 40px;
            right: 40px;
            text-align: center;
            font-size: 14px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
        }

        .stamp {
            text-align: center;
            margin-top: 40px;
        }

        .stamp-circle {
            display: inline-block;
            border: 3px solid #2563eb;
            border-radius: 50%;
            width: 100px;
            height: 100px;
            line-height: 100px;
            text-align: center;
            color: #2563eb;
            font-weight: bold;
            font-size: 16px;
            opacity: 0.7;
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

            <div class="divider">﴿ ﴾</div>

            <div class="content">
                <p>يشرفنا أن نقدم هذه الشهادة تقديراً لجهود</p>
                <p class="volunteer-name">{{ $volunteerName }}</p>
                <p>لمشاركته الفاعلة في</p>
                <p class="opportunity-title">{{ $opportunityTitle }}</p>
                <p>بمسجد</p>
                <p class="opportunity-title" style="font-size:20px;">{{ $mosqueName }}</p>
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
