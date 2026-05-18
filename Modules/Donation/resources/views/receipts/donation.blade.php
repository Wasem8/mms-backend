<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إيصال تبرع</title>

    {{-- استدعاء خط Amiri والـ Cairo لضمان مظهر عصري للأرقام والنصوص --}}
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        /* إعدادات الصفحة الصارمة للطباعة بجودة عالية A4 */
        @page {
            size: A4 portrait;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            direction: rtl;
            text-align: right;
            font-family: 'Cairo', 'Amiri', serif;
            background-color: #fff;
            color: #333;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 210mm;
            height: 297mm;
        }

        /* الحاوية الرئيسية (البرواز الأخضر) بمقاس حتمي */
        .page {
            width: 210mm;
            height: 297mm;
            padding: 30mm 20mm;
            border: 15px double #1a6b3c; /* برواز إسلامي مزدوج وأنيق */
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between; /* توزيع العناصر بشكل متناسق على طول الورقة */
        }

        /* ── الرأس ─────────────────────────────── */
        .header {
            text-align: center;
            border-bottom: 2px dashed #1a6b3c;
            padding-bottom: 7px;
            margin-bottom: 7px;
        }

        .mosque-name {
            font-size: 24px;
            font-weight: 700;
            color: #1a6b3c;
            margin-bottom: 5px;
        }

        .receipt-title {
            font-size: 28px;
            font-weight: 700;
            color: #222;
            letter-spacing: 1px;
        }

        .receipt-subtitle {
            font-size: 14px;
            color: #666;
            font-family: 'Cairo', sans-serif;
            font-weight: 400;
            margin-top: 2px;
        }

        /* ── شريط البيانات الثنائي ───────────────── */
        .reference-bar {
            background: #f4f9f5;
            border-right: 5px solid #1a6b3c;
            padding: 12px 20px;
            font-size: 14px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 4px;
        }

        .reference-bar strong {
            color: #1a6b3c;
        }

        /* ── نص الإقرار ───────────────────────────── */
        .body-text {
            font-size: 18px;
            line-height: 2.2;
            text-align: center;
            margin-bottom: 15px;
            color: #444;
            font-family: 'Amiri', serif;
        }

        .body-text strong {
            color: #1a6b3c;
            font-size: 20px;
            border-bottom: 1px solid #1a6b3c;
            padding: 0 4px;
        }

        /* ── صندوق المبلغ الاحترافي ───────────────── */
        .amount-box {
            background-color: #f4f9f5;
            border: 2px solid #1a6b3c;
            padding: 20px;
            text-align: center;
            margin: 0 auto 40px;
            width: 60%;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .amount-label {
            font-size: 13px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .amount-value {
            font-size: 32px;
            font-weight: 700;
            color: #1a6b3c;
        }

        .amount-currency {
            font-size: 16px;
            font-weight: 600;
            color: #555;
            margin-right: 5px;
        }

        /* ── جدول التفاصيل الحديث ────────────────── */
        .details-grid {
            width: 100%;
            margin-bottom: 40px;
        }

        .details-row {
            display: flex;
            justify-content: space-between;
            padding: 14px 10px;
            border-bottom: 1px solid #eaeaea;
        }

        .details-row:last-child {
            border-bottom: none;
        }

        .cell-label {
            font-weight: 600;
            color: #666;
            font-size: 14px;
        }

        .cell-value {
            color: #222;
            font-weight: 700;
            font-size: 15px;
        }

        /* ── منطقة التواقيع والأختام ───────────────── */
        .seal-container {
            display: flex;
            justify-content: space-around;
            margin-top: auto; /* يدفع التواقيع لأسفل الورقة تلقائياً */
            padding-top: 30px;
        }

        .seal-item {
            text-align: center;
            width: 150px;
        }

        .seal-box {
            border: 1px dashed #bbb;
            height: 90px;
            width: 100%;
            margin-bottom: 10px;
            border-radius: 6px;
            background: #fafafa;
        }

        .seal-item p {
            font-size: 13px;
            color: #555;
            font-weight: 600;
        }

        /* ── التذييل ─────────────────────────────── */
        .footer {
            text-align: center;
            font-size: 12px;
            color: #888;
            border-top: 1px solid #eee;
            padding-top: 15px;
            margin-top: 30px;
        }
    </style>
</head>
<body>
<div class="page">

    {{-- الرأس --}}
    <div class="header">
        <div class="mosque-name">{{ $mosque_name }}</div>
        <div class="receipt-title">إيصال تبرع</div>
        <div class="receipt-subtitle">Donation Receipt</div>
    </div>

    {{-- شريط البيانات الأساسية المتناسق --}}
    <div class="reference-bar">
        <div>رقم الإيصال: <strong>{{ $donation->reference }}</strong></div>
        <div>تاريخ الإصدار: <strong>{{ $issued_at }}</strong></div>
    </div>

    {{-- نص الإقرار والترحيب --}}
    <div class="body-text">
        نُقرّ بموجب هذا الإيصال باستلام تبرع كريم من<br>
        <strong>{{ $donor_name }}</strong><br>
        إلى <strong>{{ $target['label'] }}: {{ $target['name'] }}</strong>
    </div>

    {{-- صندوق المبلغ أو التبرع العيني المميز --}}
<div class="amount-box">
    @if($donation->donation_type === 'in_kind')
        {{-- في حال كان التبرع عيناً (مواد، أثاث، أطعمة، إلخ) --}}
        <div class="amount-label">المواد المتبرع بها (تبرع عيني)</div>
        <div class="amount-value" style="font-size: 24px;">
                        {{ number_format($donation->amount, 2) }}
            {{ $donation->item_description ?? 'مواد عينية' }}
        </div>
    @else
        {{-- في حال كان التبرع نقداً (cash) كالمعتاد --}}
        <div class="amount-label">المبلغ المتبرع به</div>
        <div class="amount-value">
            {{ number_format($donation->amount, 2) }}
            <span class="amount-currency">{{ $currency }}</span>
        </div>
    @endif
</div>

    {{-- جدول تفاصيل التبرع بنظام Flex --}}
    <div class="details-grid">
        <div class="details-row">
            <span class="cell-label">طريقة الدفع</span>
            <span class="cell-value">{{ $payment_method }}</span>
        </div>
        <div class="details-row">
            <span class="cell-label">حالة الدفع</span>
            <span class="cell-value">{{ $donation_status }}</span>
        </div>
        <div class="details-row">
            <span class="cell-label">تاريخ التبرع</span>
            <span class="cell-value">{{ $donation->created_at?->format('Y-m-d') ?? $issued_at }}</span>
        </div>
    </div>

    {{-- منطقة الأختام والتواقيع أسفل المستند --}}
    <div class="seal-container">
        <div class="seal-item">
            <div class="seal-box"></div>
            <p>ختم المسجد</p>
        </div>
        <div class="seal-item">
            <div class="seal-box"></div>
            <p>توقيع المسؤول</p>
        </div>
    </div>

    {{-- تذييل المستند الرسمي --}}
    <div class="footer">
        هذا الإيصال وثيقة رسمية صادرة عن {{ $mosque_name }} &mdash; يُرجى الاحتفاظ به للرجوع إليه عند الحاجة.
    </div>

</div>
</body>
</html>
