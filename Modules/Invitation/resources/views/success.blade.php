<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تم تفعيل الحساب بنجاح - منصة وَصْل</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Cairo', sans-serif; }
        /* نفس الخلفية المتدرجة الهادئة */
        .bg-pattern {
            background-color: #f0fdf4;
            background-image: radial-gradient(#d1fae5 1.5px, transparent 1.5px), radial-gradient(#d1fae5 1.5px, #f0fdf4 1.5px);
            background-size: 40px 40px;
            background-position: 0 0, 20px 20px;
            background: radial-gradient(circle at top right, #d1fae5, transparent 60%), radial-gradient(circle at bottom left, #fef3c7, transparent 50%), #f0fdf4;
        }
        .app-logo {
            max-height: 60px;
            width: auto;
            display: inline-block;
        }
    </style>
</head>
<body class="bg-pattern flex items-center justify-center min-h-screen p-4 md:p-8">

<div class="bg-white/95 backdrop-blur-md shadow-2xl rounded-3xl p-8 md:p-10 max-w-md w-full border border-emerald-100 text-center transform transition-all duration-500 hover:scale-[1.01]">

    <div class="mb-6">
        <img src="https://koihzqfwzvnrcrrtpnyg.supabase.co/storage/v1/object/public/images/logo.png" class="app-logo" alt="وصل">
    </div>

    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-emerald-100 text-emerald-600 mb-6 shadow-inner animate-bounce">
        <i data-lucide="check-circle-2" class="w-12 h-12"></i>
    </div>

    <h2 class="text-2xl font-bold text-slate-800 tracking-tight">مبارك، تم تفعيل حسابك بنجاح! 🎉</h2>
    <p class="text-sm text-slate-500 mt-3 leading-relaxed">
        مرحباً بك يا <span class="font-bold text-slate-700">{{ $user->name }}</span> في عائلة منصة وَصْل التعليمية. لقد تم إعداد صلاحياتك بنجاح وجاهز الآن للانطلاق.
    </p>

    <div class="mt-6 p-4 bg-slate-50 border border-slate-100 rounded-2xl text-right space-y-2">
        <div class="flex justify-between items-center text-xs">
            <span class="text-slate-400">البريد الإلكتروني:</span>
            <span class="font-semibold text-slate-700">{{ $user->email }}</span>
        </div>
        <div class="flex justify-between items-center text-xs">
            <span class="text-slate-400">مستوى الصلاحية الممنوح:</span>
            <span class="font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md">{{ $role }}</span>
        </div>
    </div>

    <div class="mt-8 space-y-3">

        <p class="text-xs text-slate-400 mt-4">يمكنك الآن تسجيل الدخول عبر تطبيق الجوال أو لوحة التحكم مباشرة.</p>
    </div>

    <div class="text-center mt-8 pt-4 border-t border-slate-100">
        <p class="text-xs text-slate-400">منصة وَصْل التعليمية الذكية لإدارة الحلقات</p>
    </div>
</div>

<script>
    // تفعيل الأيقونات
    lucide.createIcons();
</script>
</body>
</html>
