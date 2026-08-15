<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إيصال تبرع - {{ $donation->reference }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f2f5f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .receipt-card {
            background-color: #ffffff;
            width: 100%;
            max-width: 420px;
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            border: 1px solid #eaeaea;
        }
        .receipt-header {
            background-color: #129c6f;
            color: #ffffff;
            text-align: center;
            padding: 30px 20px 20px;
        }
        .icon-circle {
            width: 50px;
            height: 50px;
            background-color: #129c6f;
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 15px;
            box-shadow: 0 0 0 6px rgba(255, 255, 255, 0.15);
        }
        .icon-circle svg {
            fill: #ffffff;
            width: 24px;
            height: 24px;
        }
        .receipt-title {
            margin: 0 0 6px;
            font-size: 22px;
            font-weight: 800;
        }
        .receipt-subtitle {
            margin: 0;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
        }
        .receipt-body {
            padding: 30px 25px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 15px;
        }
        .info-row:last-of-type {
            border-bottom: none;
        }
        .info-label {
            color: #777;
            font-weight: 500;
        }
        .info-value {
            color: #111;
            font-weight: 700;
            text-align: left;
        }
        .amount-container {
            background-color: #fdfdfd;
            border: 1.5px dashed #dcdcdc;
            border-radius: 24px;
            text-align: center;
            padding: 22px;
            margin: 15px 0 25px;
        }
        .amount-label {
            color: #888;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 8px;
        }
        .amount-value {
            color: #129c6f;
            font-size: 38px;
            font-weight: 800;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: baseline;
            gap: 6px;
            direction: ltr;
        }
        .amount-currency {
            font-size: 20px;
            font-weight: 700;
        }
        .footer-message {
            text-align: center;
            color: #666;
            font-size: 13px;
            line-height: 1.6;
            margin-bottom: 25px;
            font-weight: 500;
        }
        .download-btn {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            background-color: #e6f6ee;
            color: #129c6f;
            text-decoration: none;
            padding: 14px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 15px;
            transition: all 0.2s ease;
            border: 1px solid #d1efe0;
        }
        .download-btn:hover {
            background-color: #d1efe0;
        }
        .download-btn svg {
            fill: currentColor;
            width: 20px;
            height: 20px;
        }

        /* Optional Print Styles for PDF generators */
        @media print {
            body { background-color: white; }
            .receipt-card { box-shadow: none; border: none; }
            .download-btn { display: none; }
        }
    </style>
</head>
<body>

    <div class="receipt-card">
        <div class="receipt-header">
            <div class="icon-circle">
                <svg viewBox="0 0 24 24">
                    <path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"/>
                </svg>
            </div>
            <h2 class="receipt-title">إيصال تبرع معتمد</h2>
            <p class="receipt-subtitle">نظام إدارة المساجد والشؤون الدينية</p>
        </div>

        <div class="receipt-body">

            <div class="info-row">
                <span class="info-label">رقم الإيصال:</span>
                <span class="info-value">{{ $donation->reference }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">التاريخ:</span>
                <span class="info-value">
                    {{-- Uses Carbon to format the date correctly in Arabic --}}
                    {{ \Carbon\Carbon::parse($donation->created_at)->locale('ar')->translatedFormat('d F Y في h:i a') }}
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">اسم المتبرع:</span>
                <span class="info-value">{{ $donation->donor_name ?? 'فاعل خير' }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">
                    @if($donation->campaign_title)
                        عنوان الحملة: <span style="color:#111; font-weight:700;">{{ $donation->campaign_title }}</span>
                    @else
                        نوع التبرع:
                    @endif
                </span>
                <span class="info-value">
                    {{ $donation->donation_type === 'cash' ? 'تبرع نقدي' : __('receipt.' . $donation->donation_type) }}
                </span>
            </div>

            <div class="amount-container">
                <div class="amount-label">المبلغ المستلم</div>
                <div class="amount-value">
                    <span class="amount-currency">ل.س</span>
                    {{-- Formats the amount with commas (e.g., 2,500) --}}
                    <span>{{ number_format($donation->amount) }}</span>
                </div>
            </div>

            <div class="footer-message">
                جزاكم الله خيراً وبارك في أموالكم، تقبل الله منا ومنكم صالح الأعمال.
            </div>

            {{-- Generates the download route dynamically based on the donation ID --}}
        </div>
    </div>

</body>
</html>
