<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>إيصال تبرع - {{ $donation->reference }}</title>

    <style>
        /*
        |--------------------------------------------------------------------------
        | Base
        |--------------------------------------------------------------------------
        */

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            min-height: 100%;
        }

        body {
            font-family:
                "DejaVu Sans",
                "Tahoma",
                "Arial",
                sans-serif;

            background: #f3f6f5;

            color: #171717;

            display: flex;
            justify-content: center;
            align-items: center;

            min-height: 100vh;

            padding: 20px;
        }


        /*
        |--------------------------------------------------------------------------
        | Receipt Card
        |--------------------------------------------------------------------------
        */

        .receipt-card {
            width: 100%;
            max-width: 420px;

            background: #ffffff;

            border-radius: 23px;

            overflow: hidden;

            border: 1px solid #e5e7e6;

            box-shadow:
                0 12px 30px rgba(0, 0, 0, 0.08);

            position: relative;
        }


        /*
        |--------------------------------------------------------------------------
        | Header
        |--------------------------------------------------------------------------
        */

        .receipt-header {
            height: 188px;

            background: #0c9b6d;

            color: #ffffff;

            text-align: center;

            padding: 31px 20px 20px;
        }


        /*
        |--------------------------------------------------------------------------
        | Success Icon
        |--------------------------------------------------------------------------
        */

       .icon-wrapper {
            width: 68px;
            height: 68px;

            margin: 0 auto 16px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 50%;

            /* لون خلفية شفاف أكثر نعومة */
            background: rgba(255, 255, 255, 0.15);

            /* ظل خفيف لإعطاء عمق للتصميم */
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .icon-circle {
            width: 48px;
            height: 48px;

            /* إطار أبيض واضح ونقي */
            border: 2px solid #ffffff;

            border-radius: 50%;

            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-circle svg {
            width: 24px;
            height: 24px;

            /* إزالة التعبئة واستخدام لون النص للحدود */
            fill: none;
            stroke: currentColor;
            color: #ffffff;

            stroke-width: 2.5;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        /*
        |--------------------------------------------------------------------------
        | Header Typography
        |--------------------------------------------------------------------------
        */

        .receipt-title {
            margin: 0 0 7px;

            font-size: 21px;

            line-height: 1.4;

            font-weight: 800;

            color: #ffffff;
        }

        .receipt-subtitle {
            margin: 0;

            font-size: 12px;

            line-height: 1.6;

            font-weight: 500;

            color: rgba(255, 255, 255, 0.86);
        }


        /*
        |--------------------------------------------------------------------------
        | Body
        |--------------------------------------------------------------------------
        */

        .receipt-body {
            padding: 21px 30px 30px;
        }


        /*
        |--------------------------------------------------------------------------
        | Information Rows
        |--------------------------------------------------------------------------
        */

        .info-row {
            min-height: 48px;

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 15px;

            border-bottom: 1px solid #ededed;

            font-size: 14px;
        }

        .info-row:last-of-type {
            border-bottom: none;
        }

        .info-label {
            color: #858585;

            font-weight: 500;

            white-space: nowrap;
        }

        .info-value {
            color: #171717;

            font-weight: 700;

            text-align: left;

            direction: rtl;

            line-height: 1.5;
        }


        /*
        |--------------------------------------------------------------------------
        | Receipt Reference
        |--------------------------------------------------------------------------
        */

        .reference-value {
            direction: ltr;

            text-align: left;

            font-family:
                "DejaVu Sans",
                "Arial",
                sans-serif;

            font-size: 13px;

            letter-spacing: 0.2px;
        }


        /*
        |--------------------------------------------------------------------------
        | Date
        |--------------------------------------------------------------------------
        */

        .date-value {
            white-space: nowrap;

            font-size: 13px;
        }


        /*
        |--------------------------------------------------------------------------
        | Amount / Description Box
        |--------------------------------------------------------------------------
        */

        .amount-container {
            margin-top: 15px;
            margin-bottom: 25px;

            min-height: 109px;

            background: #fdfdfd;

            border: 1.5px dashed #dddddd;

            border-radius: 24px;

            display: flex;

            flex-direction: column;

            align-items: center;

            justify-content: center;

            text-align: center;

            padding: 16px 15px;
        }

        .amount-label {
            margin-bottom: 6px;

            color: #8a8a8a;

            font-size: 13px;

            line-height: 1.5;

            font-weight: 500;
        }

        .amount-value {
            margin: 0;

            display: flex;

            align-items: baseline;

            justify-content: center;

            gap: 6px;

            direction: rtl;

            color: #0c986b;

            font-size: 32px;

            line-height: 1.2;

            font-weight: 800;

            letter-spacing: -0.5px;
        }

        .amount-number {
            direction: ltr;

            unicode-bidi: embed;
        }

        .amount-currency {
            font-size: 20px;

            font-weight: 800;

            white-space: nowrap;
        }

        .description-value {
            margin: 0;

            color: #0c986b;

            font-size: 18px;

            line-height: 1.5;

            font-weight: 700;

            padding: 0 5px;

            word-break: break-word;
        }


        /*
        |--------------------------------------------------------------------------
        | Footer Message
        |--------------------------------------------------------------------------
        */

        .footer-message {
            margin: 0 10px 25px;

            text-align: center;

            color: #777777;

            font-size: 12px;

            line-height: 1.8;

            font-weight: 500;
        }


        /*
        |--------------------------------------------------------------------------
        | Download Button
        |--------------------------------------------------------------------------
        */

        .download-btn {
            width: 100%;

            min-height: 47px;

            display: flex;

            justify-content: center;

            align-items: center;

            gap: 8px;

            background: #e5f5ef;

            color: #0c986b;

            text-decoration: none;

            border: 1px solid #c9e7dc;

            border-radius: 25px;

            font-size: 14px;

            font-weight: 700;

            transition:
                background 0.2s ease,
                transform 0.2s ease;
        }

        .download-btn:hover {
            background: #d9f0e8;
        }

        .download-btn:active {
            transform: scale(0.99);
        }

        .download-btn svg {
            width: 19px;
            height: 19px;

            fill: none;

            stroke: currentColor;

            stroke-width: 2;

            stroke-linecap: round;

            stroke-linejoin: round;
        }


        /*
        |--------------------------------------------------------------------------
        | PDF / Print
        |--------------------------------------------------------------------------
        */

        @media print {

            html,
            body {
                background: #ffffff;
            }

            body {
                padding: 0;

                min-height: auto;

                display: block;
            }

            .receipt-card {
                width: 100%;

                max-width: 420px;

                margin: 0 auto;

                border: none;

                box-shadow: none;

                border-radius: 0;
            }

            .download-btn {
                display: none;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Small Screens
        |--------------------------------------------------------------------------
        */

        @media screen and (max-width: 450px) {

            body {
                padding: 10px;
            }

            .receipt-card {
                max-width: 420px;
            }

            .receipt-body {
                padding-left: 28px;
                padding-right: 28px;
            }
        }
    </style>
</head>

<body>

    <div class="receipt-card">

        {{-- ============================================================
             Header
        ============================================================= --}}

      <div class="receipt-header">
    <div class="icon-wrapper">
        <div class="icon-circle">
            <svg viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                <path d="M7 12.5L10.2 15.5L17 8.5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>
    </div>

    <h1 class="receipt-title">إيصال تبرع معتمد</h1>
    <p class="receipt-subtitle">نظام إدارة المساجد والشؤون الدينية</p>
</div>


        {{-- ============================================================
             Receipt Body
        ============================================================= --}}

        <div class="receipt-body">


            {{-- Receipt Number --}}

            <div class="info-row">

                <span class="info-label">
                    رقم الإيصال:
                </span>

                <span class="info-value reference-value">
                    {{ $donation->reference ?? '—' }}
                </span>

            </div>


            {{-- Mosque --}}

            <div class="info-row">

                <span class="info-label">
                    اسم المسجد:
                </span>

                <span class="info-value">
                    {{ $mosque_name ?? ($mosque->name ?? '—') }}
                </span>

            </div>


            {{-- Date --}}

            <div class="info-row">

                <span class="info-label">
                    التاريخ:
                </span>

                <span class="info-value date-value">

                    @if($donation->created_at)

                        {{ \Carbon\Carbon::parse($donation->created_at)
                            ->locale('ar')
                            ->translatedFormat('d F Y في h:i a') }}

                    @else

                        —

                    @endif

                </span>

            </div>


            {{-- Donor --}}

            <div class="info-row">

                <span class="info-label">
                    اسم المتبرع:
                </span>

                <span class="info-value">
                    {{ $donation->donor_name ?? 'فاعل خير' }}
                </span>

            </div>


            {{-- Donation Type --}}

            <div class="info-row">

                <span class="info-label">
                    نوع التبرع:
                </span>

                <span class="info-value">

                    @switch($donation->donation_type)

                        @case('cash')
                            تبرع نقدي
                            @break

                        @case('in_kind')
                            تبرع عيني
                            @break

                        @default
                            {{ $donation->donation_type ?? '—' }}

                    @endswitch

                </span>

            </div>


            {{-- ========================================================
                 Amount (cash) / Description (in_kind)
            ========================================================= --}}

            <div class="amount-container">

                @if($donation->donation_type === 'in_kind')

                    <div class="amount-label">
                        وصف التبرع
                    </div>

                    <p class="description-value">
                        {{ $donation->item_description ?? '—' }}
                    </p>

                @else

                    <div class="amount-label">
                        المبلغ المستلم
                    </div>

                    <div class="amount-value">

                        <span class="amount-number">
                            {{ number_format((float) $donation->amount) }}
                        </span>

                        <span class="amount-currency">
                            ل.س
                        </span>

                    </div>

                @endif

            </div>


            {{-- ========================================================
                 Footer Message
            ========================================================= --}}

            <div class="footer-message">

                جزاكم الله خيراً وبارك في أموالكم،
                تقبل الله منا ومنكم صالح الأعمال.

            </div>


            {{-- ========================================================
                 Download PDF
            ========================================================= --}}


        </div>

    </div>

</body>

</html>
