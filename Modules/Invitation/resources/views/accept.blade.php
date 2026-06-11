<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفعيل الحساب و قبول الدعوة - منصة وَصْل</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Cairo', sans-serif; }
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

<div class="bg-white/95 backdrop-blur-md shadow-2xl rounded-3xl p-6 md:p-10 max-w-md w-full border border-emerald-100 transform transition-all duration-500">

    <div class="text-center mb-8">
        <div class="mb-5">
            <img src="https://koihzqfwzvnrcrrtpnyg.supabase.co/storage/v1/object/public/images/logo.png" class="app-logo" alt="وصل">
        </div>
        <h2 class="text-2xl font-bold text-slate-800 tracking-tight">مرحباً بك في منصة وَصْل ✨</h2>

        @if (!isset($is_expired) || !$is_expired)
            <p class="text-xs md:text-sm text-slate-500 mt-2 leading-relaxed">أنت على وشك تفعيل حسابك التعليمي الموحد بالبريد الإلكتروني التالي:</p>
            <div class="mt-3 inline-flex items-center gap-2 px-4 py-1.5 bg-emerald-50 border border-emerald-100 rounded-full text-emerald-800 text-xs font-medium">
                <i data-lucide="mail" class="w-3.5 h-3.5 text-emerald-600"></i>
                <span>{{ $invitation->email }}</span>
            </div>
        @endif
    </div>

    @if (isset($is_expired) && $is_expired)
        <div class="text-center py-6">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-rose-100 text-rose-600 mb-4 shadow-sm">
                <i data-lucide="shield-x" class="w-8 h-8"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800">رابط الدعوة غير صالح أو منتهي</h3>
            <p class="text-xs text-slate-500 mt-2 leading-relaxed px-2">
                عذراً، يبدو أن صلاحية هذا الرابط قد انتهت (صلاحية الروابط 7 أيام فقط)، أو تم استخدامه مسبقاً لتفعيل الحساب.
            </p>
            <div class="mt-6">
                <p class="text-xs text-emerald-700 font-semibold bg-emerald-50 inline-block px-4 py-2 rounded-xl border border-emerald-100">
                    يرجى طلب دعوة جديدة من مشرف منصة وَصْل بالمسجد.
                </p>
            </div>
        </div>
    @else

        @if (isset($errors) && $errors->any())
            <div class="bg-rose-50 border-r-4 border-rose-500 text-rose-800 p-4 rounded-xl mb-6 text-sm shadow-sm">
                <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-700 pr-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="activationForm" action="{{ route('invitations.accept_process') }}" method="POST" class="space-y-5" novalidate>
            @csrf
            <input type="hidden" name="token" value="{{ $invitation->token }}">

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                    <i data-lucide="user" class="w-3.5 h-3.5 text-emerald-400"></i>
                    الاسم الكامل
                </label>
                <div class="relative">
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           class="w-full pl-4 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500 focus:bg-white transition-all duration-300 font-medium text-slate-800"
                           placeholder="أدخل اسمك الكامل">
                </div>
                <p id="nameError" class="text-rose-500 text-xs mt-1.5 hidden animate-fade-in"></p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                    <i data-lucide="lock" class="w-3.5 h-3.5 text-emerald-400"></i>
                    كلمة المرور الجديدة
                </label>
                <div class="relative">
                    <input type="password" id="password" name="password" required
                           class="w-full pl-4 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500 focus:bg-white transition-all duration-300 text-slate-800"
                           placeholder="••••••••">
                </div>
                <p id="passwordError" class="text-rose-500 text-xs mt-1.5 hidden animate-fade-in"></p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-400"></i>
                    تأكيد كلمة المرور
                </label>
                <div class="relative">
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                           class="w-full pl-4 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500 focus:bg-white transition-all duration-300 text-slate-800"
                           placeholder="••••••••">
                </div>
                <p id="confirmationError" class="text-rose-500 text-xs mt-1.5 hidden animate-fade-in"></p>
            </div>

            <button type="submit" id="submitBtn"
                    class="w-full mt-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3.5 px-4 rounded-xl text-sm transition-all duration-300 shadow-xl shadow-emerald-200 hover:shadow-none cursor-pointer flex items-center justify-center gap-2 group">
                <span>تأكيد وبناء الحساب الموحد</span>
                <i data-lucide="arrow-left" class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform duration-300"></i>
            </button>
        </form>

        <div class="text-center mt-8 pt-5 border-t border-slate-100">
            <div class="inline-flex items-center gap-1.5 text-xs text-slate-400 bg-amber-50/70 border border-amber-100/80 px-4 py-2 rounded-xl">
                <i data-lucide="shield-alert" class="w-3.5 h-3.5 text-amber-600"></i>
                <span>سيتم منحك صلاحيات النظام بمستوى:</span>
                <span class="font-bold text-amber-800 bg-amber-100 px-2 py-0.5 rounded-md">{{ $invitation->role }}</span>
            </div>
        </div>
    @endif

</div>

<script>
    lucide.createIcons();

    // جلب عناصر الحقول والتنبيهات
    const form = document.getElementById('activationForm');
    if (form) {
        const nameInput = document.getElementById('name');
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('password_confirmation');

        const nameError = document.getElementById('nameError');
        const passwordError = document.getElementById('passwordError');
        const confirmationError = document.getElementById('confirmationError');

        // دالة مساعدة لإظهار الخطأ وتغيير إطار المدخل
        function showError(input, errorElement, message) {
            errorElement.innerText = message;
            errorElement.classList.remove('hidden');
            input.classList.add('border-rose-400', 'bg-rose-50/30');
            input.classList.remove('focus:border-emerald-500', 'border-slate-200');
        }

        // دالة مساعدة لإخفاء وتنظيف الأخطاء عند الكتابة الصحيحة
        function clearError(input, errorElement) {
            errorElement.innerText = '';
            errorElement.classList.add('hidden');
            input.classList.remove('border-rose-400', 'bg-rose-50/30');
            input.classList.add('border-slate-200');
        }

        // 1. فحص تفاعلي لحظي لحقل الاسم
        function validateName() {
            if (nameInput.value.trim() === '') {
                showError(nameInput, nameError, 'حقل الاسم الكامل مطلوب ولا يمكن تركه فارغاً.');
                return false;
            } else if (nameInput.value.trim().length < 3) {
                showError(nameInput, nameError, 'الاسم الكامل يجب أن يتكون من 3 أحرف على الأقل.');
                return false;
            }
            clearError(nameInput, nameError);
            return true;
        }

        // 2. فحص تفاعلي لحظي لحقل كلمة المرور
        function validatePassword() {
            if (passwordInput.value === '') {
                showError(passwordInput, passwordError, 'حقل كلمة المرور مطلوب.');
                return false;
            } else if (passwordInput.value.length < 6) {
                showError(passwordInput, passwordError, 'كلمة المرور الجديدة يجب ألا تقل عن 6 أحرف أو رموز.');
                return false;
            }
            clearError(passwordInput, passwordError);
            // إعادة فحص التطابق إذا كان حقل التأكيد ممتلئاً بالفعل
            if (confirmInput.value !== '') validateConfirmation();
            return true;
        }

        // 3. فحص تفاعلي لحظي لحقل تأكيد كلمة المرور
        function validateConfirmation() {
            if (confirmInput.value === '') {
                showError(confirmInput, confirmationError, 'يرجى تأكيد كلمة المرور.');
                return false;
            } else if (passwordInput.value !== confirmInput.value) {
                showError(confirmInput, confirmationError, 'كلمة المرور وتأكيد كلمة المرور غير متطابقين.');
                return false;
            }
            clearError(confirmInput, confirmationError);
            return true;
        }

        // ربط الأحداث للحقول لتعمل فوراً أثناء ضغط الأزرار والكتابة (Input Event)
        nameInput.addEventListener('input', validateName);
        passwordInput.addEventListener('input', validatePassword);
        confirmInput.addEventListener('input', validateConfirmation);

        // اعتراض عملية الإرسال للتأكد الشامل قبل التوجه للسيرفر
        form.addEventListener('submit', function (e) {
            const isNameValid = validateName();
            const isPasswordValid = validatePassword();
            const isConfirmValid = validateConfirmation();

            // إذا وجد أي خطأ، امنع إرسال الصفحة نهائياً
            if (!isNameValid || !isPasswordValid || !isConfirmValid) {
                e.preventDefault();

                // هزة خفيفة بصرياً للبطاقة لتنبيه المستخدم وجود خطأ
                form.parentElement.classList.add('animate-shake');
                setTimeout(() => form.parentElement.classList.remove('animate-shake'), 500);
            }
        });
    }
</script>
</body>
</html>
