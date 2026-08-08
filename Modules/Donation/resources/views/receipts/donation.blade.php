<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إيصال تبرع</title>
    <style>
        body {
            font-family: 'xbriyaz', sans-serif;
            direction: rtl;
            margin: 0;
            padding: 0;
            color: #1e293b;
            background: #ffffff;
        }

        .page {
            padding: 10mm 12mm;
        }

        /* ── الإطار الخارجي ── */
        .receipt-box {
            border: 3px solid #15803d;
            padding: 6px;
            background: #f8fdf9;
        }

        .receipt-inner {
            border: 1px solid #86efac;
            padding: 30px 25px;
        }

        /* ── الهيدر ── */
        .header {
            text-align: center;
            margin-bottom: 8px;
        }

        .mosque-name {
            font-size: 18px;
            color: #166534;
            font-weight: bold;
            margin: 0 0 10px 0;
        }

        .basmala {
            font-size: 15px;
            color: #15803d;
            margin: 0 0 8px 0;
        }

        .title {
            font-size: 32px;
            color: #14532d;
            font-weight: bold;
            margin: 0 0 4px 0;
        }

        .subtitle {
            font-size: 13px;
            color: #64748b;
            margin: 0 0 16px 0;
        }

        /* ── الخط المنقط ── */
        .dashed-line {
            border: none;
            border-top: 2px dashed #15803d;
            margin: 0 0 16px 0;
        }

        /* ── شريط المعلومات ── */
        .info-bar {
            background: #f0fdf4;
            border-right: 4px solid #15803d;
            padding: 10px 16px;
            margin-bottom: 24px;
        }

        .info-bar table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-bar td {
            font-size: 13px;
            color: #166534;
            font-weight: bold;
        }

        .info-bar td:first-child {
            text-align: right;
        }

        .info-bar td:last-child {
            text-align: left;
        }

        /* ── المحتوى الرئيسي ── */
        .content {
            text-align: center;
            margin-bottom: 24px;
        }

        .content p {
            font-size: 15px;
            color: #475569;
            margin: 6px 0;
            line-height: 1.8;
        }

        .donor-name {
            font-size: 26px;
            color: #15803d;
            font-weight: bold;
            margin: 10px auto;
            padding-bottom: 4px;
            border-bottom: 2px solid #bbf7d0;
            display: inline-block;
        }

        .target-info {
            font-size: 17px;
            color: #14532d;
            font-weight: bold;
            margin: 8px auto;
            padding-bottom: 4px;
            border-bottom: 1px solid #86efac;
            display: inline-block;
        }

        /* ── صندوق المبلغ ── */
        .amount-box {
            background: #f0fdf4;
            border: 2px dashed #22c55e;
            padding: 20px;
            text-align: center;
            margin: 0 auto 28px;
            width: 70%;
        }

        .amount-label {
            font-size: 14px;
            color: #166534;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .amount-value {
            font-size: 34px;
            font-weight: bold;
            color: #15803d;
        }

        .amount-currency {
            font-size: 18px;
            color: #166534;
            font-weight: bold;
            margin-left: 6px;
        }

        /* ── جدول التفاصيل ── */
        .details-table {
            width: 85%;
            margin: 0 auto 30px;
            border-collapse: collapse;
        }

        .details-table tr {
            border-bottom: 1px solid #dcfce7;
        }

        .details-table td {
            padding: 10px 12px;
            font-size: 14px;
        }

        .details-table td:first-child {
            text-align: right;
            color: #64748b;
            width: 50%;
        }

        .details-table td:last-child {
            text-align: left;
            color: #0f172a;
            font-weight: bold;
        }

        /* ── التوقيع والختم ── */
        .signatures {
            width: 100%;
            margin-top: 40px;
            border-collapse: collapse;
        }

        .signatures td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            padding-top: 20px;
        }

        .stamp-box {
            border: 2px solid #15803d;
            padding: 8px 16px;
            display: inline-block;
            color: #15803d;
            font-weight: bold;
            font-size: 14px;
        }

        .sig-line {
            border-top: 1px solid #94a3b8;
            width: 120px;
            margin: 0 auto 6px;
            padding-top: 6px;
            color: #64748b;
            font-size: 13px;
            font-weight: bold;
        }

        /* ── التذييل ── */
        .footer {
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            border-top: 1px solid #dcfce7;
            padding-top: 12px;
            margin-top: 35px;
        }
    </style>
</head>
<body>

    <div class="page">
        <div class="receipt-box">
            <div class="receipt-inner">

                <!-- الهيدر -->
                <div class="header">
                    <div class="mosque-name">{{ $mosque_name }}</div>
                    <div class="basmala">بِسْمِ اللَّهِ الرَّحْمَـٰنِ الرَّحِيمِ</div>
                    <div class="title">إيصال تبرع</div>
                    <div class="subtitle">Donation Receipt</div>
                </div>

                <hr class="dashed-line">

                <!-- شريط المعلومات -->
                <div class="info-bar">
                    <table>
                        <tr>
                            <td>رقم الإيصال: {{ $donation->reference }}</td>
                            <td>تاريخ الإصدار: {{ $issued_at }}</td>
                        </tr>
                    </table>
                </div>

                <!-- المحتوى -->
                <div class="content">
                    <p>نُقَرُّ بِمُوجَبِ هَذَا الْإِيصَالِ بِاسْتِلامِ تَبَرُّعٍ كَرِيمٍ مِنْ</p>
                    <div class="donor-name">{{ $donor_name }}</div>
                    <p>تَمَّ تَخْصِيصُهُ لِصَالِحِ</p>
                    <div class="target-info">{{ $target['label'] }}: {{ $target['name'] }}</div>
                </div>

                <!-- المبلغ -->
                <div class="amount-box">
                    @if($donation->donation_type === 'in_kind')
                        <div class="amount-label">المواد المتبرع بها (عيني)</div>
                        <div class="amount-value" style="font-size: 22px;">
                            {{ number_format((float) ($donation->amount ?? 0), 2) }}
                            {{ $donation->item_description ?? 'مواد عينية' }}
                        </div>
                    @else
                        <div class="amount-label">المبلغ المتبرع به</div>
                        <div>
                            <span class="amount-currency">{{ $currency }}</span>
                            <span class="amount-value">{{ number_format((float) ($donation->amount ?? 0), 2) }}</span>
                        </div>
                    @endif
                </div>

                <!-- التفاصيل -->
                <table class="details-table">
                    <tr>
                        <td>طريقة الدفع</td>
                        <td>{{ $payment_method }}</td>
                    </tr>
                    <tr>
                        <td>حالة الدفع</td>
                        <td>{{ $donation_status }}</td>
                    </tr>
                </table>

                <!-- التوقيع والختم -->
                <table class="signatures">
                    <tr>
                        <td>
                            <div class="stamp-box">ختم المسجد</div>
                        </td>
                        <td>
                            <div class="sig-line">توقيع المسؤول</div>
                        </td>
                    </tr>
                </table>

                <!-- التذييل -->
                <div class="footer">
                    تم إصدار هذا الإيصال إلكترونياً عبر نظام إدارة المساجد — يُرجى الاحتفاظ به للرجوع إليه عند الحاجة.
                </div>

            </div>
        </div>
    </div>

</body>
</html>
