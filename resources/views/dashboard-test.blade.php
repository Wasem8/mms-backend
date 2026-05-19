<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختبار لوحة تحكم منطقة وصل</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8 font-sans">

    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">مؤشرات أداء مساجد المنطقة (وصل)</h1>

            <div class="flex items-center gap-3">
                <label for="date-picker" class="font-semibold text-gray-600">اختر التاريخ:</label>
                <input type="date" id="date-picker" class="p-2 border rounded-md shadow-sm" value="">
                <button onclick="fetchKPIs()" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition">
                    تحديث البيانات
                </button>
            </div>
        </div>

        <div id="loading" class="text-center text-gray-500 font-bold my-10 hidden">
            جاري جلب البيانات من الـ Data Warehouse...
        </div>

        <div id="kpi-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            </div>
    </div>

    <script>
        // دالة لجلب البيانات من الـ API الخاص بك
        async function fetchKPIs() {
            const dateInput = document.getElementById('date-picker').value;
            // تحويل صيغة التاريخ من YYYY-MM-DD إلى YYYYMMDD لتطابق الـ time_id
            const timeId = dateInput ? dateInput.replace(/-/g, '') : '';

            const loading = document.getElementById('loading');
            const grid = document.getElementById('kpi-grid');

            loading.classList.remove('hidden');
            grid.innerHTML = ''; // مسح البيانات القديمة

            try {
                // ضع الرابط الصحيح للـ API الخاص بك هنا
                const response = await fetch(`/api/region-kpis?date=${timeId}`);
                const result = await response.json();

                if(result.success && result.data.metrics) {
                    const metrics = result.data.metrics;
                    renderCards(metrics);
                } else {
                    grid.innerHTML = `<p class="text-red-500 col-span-3">لا توجد بيانات لهذا اليوم (ربما لم يعمل الـ ETL).</p>`;
                }
            } catch (error) {
                console.error('Error fetching data:', error);
                grid.innerHTML = `<p class="text-red-500 col-span-3">حدث خطأ في الاتصال بالـ API.</p>`;
            } finally {
                loading.classList.add('hidden');
            }
        }

        // دالة لرسم البطاقات
        function renderCards(metrics) {
            const grid = document.getElementById('kpi-grid');

            const cards = [
                { title: 'المساجد النشطة', value: metrics.active_mosques_count || 0, color: 'text-blue-600', bg: 'bg-blue-100' },
                { title: 'الحلقات النشطة', value: metrics.region_active_halaqas || 0, color: 'text-green-600', bg: 'bg-green-100' },
                { title: 'الحملات النشطة', value: metrics.region_active_campaigns || 0, color: 'text-purple-600', bg: 'bg-purple-100' },
                { title: 'إجمالي التبرعات (عدد)', value: metrics.region_donations_count || 0, color: 'text-orange-600', bg: 'bg-orange-100' },
                { title: 'إجمالي الإيرادات (ريال)', value: metrics.region_total_revenue || 0, color: 'text-emerald-600', bg: 'bg-emerald-100' },
                { title: 'نسبة الحضور', value: (metrics.region_attendance_percentage || 0) + '%', color: 'text-indigo-600', bg: 'bg-indigo-100' },
            ];

            let html = '';
            cards.forEach(card => {
                html += `
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center ${card.bg}">
                                <span class="text-xl font-bold ${card.color}">#</span>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-700">${card.title}</h3>
                        </div>
                        <p class="text-3xl font-bold text-gray-900">${card.value}</p>
                    </div>
                `;
            });

            grid.innerHTML = html;
        }

        // تحديد تاريخ الأمس كقيمة افتراضية عند تحميل الصفحة
        window.onload = () => {
            const yesterday = new Date();
            yesterday.setDate(yesterday.getDate() - 1);
            document.getElementById('date-picker').value = yesterday.toISOString().split('T')[0];
            fetchKPIs(); // جلب البيانات تلقائياً
        };
    </script>
</body>
</html>
