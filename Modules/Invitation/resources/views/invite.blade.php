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
            max-width: 600px; /* 🎯 تم التصحيح إلى 600 بكسل ليعرض بشكل طبيعي وعريض */
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
        }
        .email-greeting {
            font-size: 20px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 20px;
        }
        .email-text {
            font-size: 15px;
            line-height: 1.8;
            color: #475569;
            margin-bottom: 15px;
        }
        .role-badge {
            display: inline-block;
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            padding: 6px 16px;
            border-radius: 50px;
            font-weight: bold;
            font-size: 14px;
            margin-top: 8px;
        }
        .button-container {
            text-align: center;
            margin: 35px 0;
        }
        .action-button {
            display: inline-block;
            background-color: #059669; /* اللون الأخضر الزمردي */
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
        /* لضمان التوافق الكامل مع شاشات الهواتف الذكية */
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
<body>

<div class="email-container">
    <div class="email-header">
        <img src="{{ $logoUrl }}" class="email-logo" alt="منصة وصل">
    </div>

    <div class="email-body">
        <div class="email-greeting">مرحباً بك في عائلة وَصْل! 👋</div>

        <p class="email-text">
            يسعدنا إبلاغك بأنه قد تم دعوتك للانضمام إلى نظامنا التعليمي الموحد والمساهمة في إدارة وتطوير الحلقات التعليمية والقرآنية.
        </p>

        <p class="email-text">
            الدور الوظيفي والصلاحية المتاحة لك على النظام هي:
            <br>
            <span class="role-badge">{{ $invitation->role }}</span>
        </p>

        <div class="button-container">
            <a href="{{ $acceptUrl }}" class="action-button">قبول الدعوة وإنشاء الحساب</a>
        </div>

        <p class="email-text" style="font-size: 13px; color: #64748b; margin-bottom: 5px;">
            ⚠️ يرجى العلم أن صلاحية هذا الرابط مؤقتة (تصل إلى 7 أيام فقط)، وستنتهي قريباً لحماية حسابك التعليمي.
        </p>

        <p class="email-text" style="font-size: 13px; color: #64748b;">
            إذا واجهتك أي مشكلة أثناء تفعيل الحساب، يرجى التواصل مباشرة مع مشرف المنصة بالمسجد.
        </p>
    </div>

    <div class="email-footer">
        منصة وَصْل التعليمية الذكية لإدارة الحلقات © {{ date('Y') }}
    </div>
</div>

</body>
</html>
