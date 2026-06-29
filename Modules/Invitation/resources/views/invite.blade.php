<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دعوة للانضمام إلى منصة وَصْل</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4fbf7;
            margin: 0;
            padding: 0;
            direction: rtl;
            text-align: right;
            -webkit-font-smoothing: antialiased;
        }
        .email-container {
            max-width: 600px;
            width: 100%;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid #d1fae5;
            box-shadow: 0 4px 20px rgba(16, 185, 129, 0.05);
        }
        .email-header {
            background-color: #ffffff;
            padding: 30px;
            text-align: center;
            border-bottom: 1px solid #f0fdf4;
        }
        .email-logo {
            max-height: 60px;
            width: auto;
            display: inline-block;
        }
        .email-body {
            padding: 40px 30px;
            color: #334155;
            text-align: right; /* ضمان محاذاة النص لليمين */
        }
        .email-greeting {
            font-size: 20px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 20px;
            text-align: right;
        }
        .email-text {
            font-size: 15px;
            line-height: 1.8;
            color: #475569;
            margin-bottom: 15px;
            text-align: right;
        }
        .role-badge, .mosque-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 50px;
            font-weight: bold;
            font-size: 14px;
            margin-top: 8px;
        }
        .role-badge {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }
        .mosque-badge {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
        }
        .button-container {
            text-align: center;
            margin: 35px 0;
        }
        .action-button {
            display: inline-block;
            background-color: #059669;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 35px;
            font-size: 15px;
            font-weight: bold;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(5, 150, 105, 0.2);
        }
        .email-footer {
            background-color: #fafdfb;
            padding: 25px;
            text-align: center;
            border-top: 1px solid #f0fdf4;
            font-size: 12px;
            color: #94a3b8;
        }
        @media only screen and (max-width: 620px) {
            .email-container {
                margin: 10px !important;
                width: auto !important;
                max-width: 100% !important;
                border-radius: 16px !important;
            }
            .email-body {
                padding: 25px 20px !important;
            }
        }
    </style>
</head>
<body style="direction: rtl; text-align: right; background-color: #f4fbf7;">

<table width="100%" border="0" cellspacing="0" cellpadding="0" style="direction: rtl; text-align: right; background-color: #f4fbf7;">
    <tr>
        <td align="center">

            <div class="email-container" dir="rtl">
                <div class="email-header">
                    <img src="{{ $logoUrl }}" class="email-logo" alt="منصة وصل">
                </div>

                <div class="email-body" style="direction: rtl; text-align: right;">
                    <div class="email-greeting">مرحباً بك في عائلة وَصْل! 👋</div>

                    <p class="email-text">
                        يسعدنا إبلاغك بأنه قد تم دعوتك للانضمام إلى نظامنا التعليمي الموحد والمساهمة في إدارة وتطوير الحلقات التعليمية والقرآنية.
                    </p>

                    @if($invitation->mosque)
                        <p class="email-text">
                            المسجد التابع له:
                            <br>
                            <span class="mosque-badge">🕌 {{ $invitation->mosque->name }}</span>
                        </p>
                    @endif

                    <p class="email-text">
                        الدور الوظيفي والصلاحية المتاحة لك على النظام هي:
                        <br>
                        <span class="role-badge">{{ $invitation->role }}</span>
                    </p>

                    <div class="button-container">
                        <a href="{{ $acceptUrl }}" class="action-button">قبول الدعوة وإنشاء الحساب</a>
                    </div>

                    <p class="email-text" style="font-size: 13px; color: #64748b; margin-bottom: 5px; text-align: right;">
                        ⚠️ يرجى العلم أن صلاحية هذا الرابط مؤقتة (تصل إلى 7 أيام فقط)، وستنتهي قريباً لحماية حسابك التعليمي.
                    </p>

                    <p class="email-text" style="font-size: 13px; color: #64748b; text-align: right;">
                        إذا واجهتك أي مشكلة أثناء تفعيل الحساب، يرجى التواصل مباشرة مع مشرف المنصة بالمسجد.
                    </p>
                </div>

                <div class="email-footer">
                    منصة وَصْل التعليمية الذكية لإدارة الحلقات © {{ date('Y') }}
                </div>
            </div>

        </td>
    </tr>
</table>

</body>
</html>
