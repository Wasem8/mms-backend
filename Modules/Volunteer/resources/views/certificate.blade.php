<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <style>
        /* مهم لـ dompdf: تحديد مقاس A4 بالضبط عشان الشهادة تطلع بصفحة واحدة بدون قطع أو فراغات زايدة */
        @page {
            size: A4;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'cairo', 'DejaVu Sans', sans-serif;
            direction: rtl;
            margin: 0;
            padding: 0;
            color: #1e293b;
            background: #ffffff;
            width: 210mm;
            height: 297mm;
        }

        .page {
            width: 210mm;
            height: 297mm;
            padding: 12mm;
            position: relative;
        }

        /* الإطار الخارجي الذهبي المزدوج */
        .outer-frame {
            width: 100%;
            height: 100%;
            border: 3px solid #1a6b3c;
            padding: 6px;
            position: relative;
        }

        .inner-frame {
            width: 100%;
            height: 100%;
            border: 1px solid #1a6b3c;
            padding: 14mm 16mm;
            position: relative;
            background: #fffdf7;
        }

        /* زخارف الزوايا الأربعة */
        .corner {
            position: absolute;
            width: 26px;
            height: 26px;
            border: 3px solid #1a6b3c;
        }
        .corner-tl { top: -3px; right: -3px; border-left: none; border-bottom: none; }
        .corner-tr { top: -3px; left: -3px; border-right: none; border-bottom: none; }
        .corner-bl { bottom: -3px; right: -3px; border-left: none; border-top: none; }
        .corner-br { bottom: -3px; left: -3px; border-right: none; border-top: none; }

        /* رقم الشهادة أعلى الصفحة */
        .cert-serial {
            position: absolute;
            top: 6mm;
            left: 8mm;
            font-size: 10px;
            color: #94a3b8;
            letter-spacing: 1px;
        }

        .header {
            text-align: center;
            margin-bottom: 6mm;
        }

        .basmala {
            font-size: 15px;
            color: #2563eb;
            font-weight: 700;
            margin: 0 0 8mm 0;
        }

        .logo-badge {
            width: 62px;
            height: 62px;
            border-radius: 50%;
            border: 2px solid #1a6b3c;
            margin: 0 auto 6mm auto;
            display: table;
            background: #fef3c7;
        }
        .logo-badge span {
            display: table-cell;
            text-align: center;
            vertical-align: middle;
            font-size: 11px;
            font-weight: 700;
            color: #92400e;
        }

        .title {
            font-size: 34px;
            color: #92400e;
            font-weight: 800;
            margin: 0 0 3mm 0;
            letter-spacing: 1px;
        }

        .subtitle {
            font-size: 13px;
            color: #64748b;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 3px;
        }

        .ornament-divider {
            text-align: center;
            margin: 8mm 0;
            color: #d97706;
            font-size: 12px;
        }
        .ornament-divider::before,
        .ornament-divider::after {
            content: "";
            display: inline-block;
            width: 60px;
            height: 1px;
            background: #d97706;
            vertical-align: middle;
            margin: 0 10px;
        }

        .content {
            text-align: center;
            margin: 6mm 0;
        }

        .content p {
            font-size: 15px;
            line-height: 1.9;
            margin: 2mm 0;
            color: #334155;
        }

        .volunteer-name {
            font-size: 30px;
            color: #1e40af;
            font-weight: 800;
            margin: 5mm 0 !important;
            padding-bottom: 3mm;
            display: inline-block;
            border-bottom: 2px solid #bfdbfe;
        }

        .opportunity-title {
            font-size: 18px !important;
            color: #0f172a;
            font-weight: 700;
        }

        .mosque-name {
            font-size: 16px !important;
            color: #92400e;
            font-weight: 700;
        }

        /* جدول التفاصيل: عدد الساعات وتاريخ الإصدار */
        .details-table {
            width: 70%;
            margin: 8mm auto;
            border-collapse: collapse;
        }

        .details-table td {
            padding: 3mm 6mm;
            font-size: 13px;
        }

        .details-table td:first-child {
            font-weight: 700;
            color: #64748b;
            text-align: left;
            width: 50%;
        }

        .details-table td:last-child {
            color: #0f172a;
            font-weight: 700;
            text-align: right;
        }

        /* منطقة التوقيعات: مدير النظام + ختم الاعتماد */
        .signatures {
            width: 100%;
            margin-top: 12mm;
        }

        .signatures table {
            width: 100%;
        }

        .sig-cell {
            text-align: center;
            width: 33%;
        }

        .sig-line {
            width: 70%;
            margin: 0 auto 2mm auto;
            border-top: 1px solid #94a3b8;
            padding-top: 2mm;
        }

        .sig-label {
            font-size: 11px;
            color: #64748b;
        }

        .stamp-circle {
            display: inline-block;
            border: 2.5px solid #2563eb;
            border-radius: 50%;
            width: 78px;
            height: 78px;
            line-height: 74px;
            text-align: center;
            color: #2563eb;
            font-weight: 800;
            font-size: 13px;
            opacity: 0.75;
            transform: rotate(-8deg);
        }

        /* تذييل الصفحة: كود التحقق */
        .footer {
            position: absolute;
            bottom: 10mm;
            left: 16mm;
            right: 16mm;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 3mm;
        }

        .footer .verify-code {
            font-family: 'DejaVu Sans Mono', monospace;
            color: #b45309;
            letter-spacing: 1px;
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="outer-frame">
            <div class="inner-frame">
                <span class="corner corner-tl"></span>
                <span class="corner corner-tr"></span>
                <span class="corner corner-bl"></span>
                <span class="corner corner-br"></span>

                <div class="cert-serial">شهادة رقم: {{ $certificateNumber ?? 'WASL-' . now()->format('Ymd') }}</div>

                <div class="header">
                    <p class="basmala">بِسْمِ اللَّهِ الرَّحْمَـٰنِ الرَّحِيمِ</p>
                    <div class="logo-badge"><span>وصل</span></div>
                    <h1 class="title">شهادة شكر وتقدير</h1>
                    <p class="subtitle">نظام إدارة المساجد</p>
                </div>

                <div class="ornament-divider">﴿ ٱلْعَمَلُ الصَّالِحُ ﴾</div>

                <div class="content">
                    <p>يشرفنا أن نقدم هذه الشهادة تقديراً واعترافاً بجهود</p>
                    <p class="volunteer-name">{{ $volunteerName }}</p>
                    <p>لمشاركته الفاعلة والمتميزة في</p>
                    <p class="opportunity-title">{{ $opportunityTitle }}</p>
                    <p>بمسجد</p>
                    <p class="mosque-name">{{ $mosqueName }}</p>
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

                <div class="signatures">
                    <table>
                        <tr>
                            <td class="sig-cell">
                                <div class="sig-line"></div>
                                <div class="sig-label">مدير المسجد</div>
                            </td>
                            <td class="sig-cell">
                                <div class="stamp-circle">معتمد<br>إلكترونياً</div>
                            </td>
                            <td class="sig-cell">
                                <div class="sig-line"></div>
                                <div class="sig-label">نظام إدارة المساجد</div>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="footer">
                    تم إصدار هذه الشهادة إلكترونياً عبر نظام إدارة المساجد (WASL) ولا تحتاج توقيعاً يدوياً للاعتماد
                    <br>
                    <span class="verify-code">كود التحقق: {{ $verificationCode ?? strtoupper(substr(md5($volunteerName.$issuedAt), 0, 10)) }}</span>
                </div>
            </div>
        </div>
    </div>
</body>

</html>

