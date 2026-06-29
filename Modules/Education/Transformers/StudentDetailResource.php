<?php

namespace Modules\Education\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        // 1. حسابات الحضور الحالية الخاصة بك
        $total_records = ($this->total_present ?? 0) + ($this->total_absent ?? 0);
        $attendance_rate = $total_records > 0
            ? round(($this->total_present / $total_records) * 100, 2) . '%'
            : '0%';

        // 2. 🎯 حسابات التقدير العام (Evaluation Summary) القادمة من الـ Service
        $avg_score = $this->evaluations_avg_score ?? 0;

        // بناء منطق الحدود العادلة للتقدير المقابل بالنسب المئوية
        $label = match (true) {
            $avg_score >= 90 => 'excellent',   // ممتاز
            $avg_score >= 80 => 'very_good',   // جيد جداً
            $avg_score >= 65 => 'good',        // جيد
            $avg_score >= 50 => 'acceptable',  // مقبول
            default          => 'acceptable',  // الافتراضي حماية للنظام من القيمة الصفرية

        };

        $lastEvaluation = $this->evaluations->first(); // لأننا رتبناهم مسبقاً بالأحدث

        $lastSura = $lastEvaluation?->surah_name ?? 'لم يبدأ بعد';

        // 🎯 2. مصفوفة ذكية لتحديد رقم واسم الجزء تقريبياً بناءً على اسم السورة المرسخة في النظام
        // يمكنك التوسع فيها لاحقاً بوضع كل السور، هنا وضعت لك أمثلة شهيرة:
        $juzMapping = match ($lastSura) {
            'النبأ', 'النازعات', 'عبس', 'التكوير', 'الانفطار', 'المطففين', 'الانشقاق', 'البروج', 'الطارق', 'الأعلى', 'الغاشية', 'الفجر', 'البلد', 'الشمس', 'الليل', 'الضحى', 'الشرح', 'التين', 'العلق', 'القدر', 'البينة', 'الزلزلة', 'العاديات', 'القارعة', 'التكاثر', 'العصر', 'الهمزة', 'الفيل', 'قريش', 'الماعون', 'الكوثر', 'الكافرون', 'النصر', 'المسد', 'الإخلاص', 'الفلق', 'الناس' => ['number' => 30, 'name' => 'جزء عمّ'],
            'الملك', 'القلم', 'الحاقة', 'المعارج', 'نوح', 'الجن', 'المزمل', 'المدثر', 'القيامة', 'الإنسان', 'المرسلات' => ['number' => 29, 'name' => 'جزء تبارك'],
            'المجادلة', 'الحشر', 'الممتحنة', 'الصف', 'الجمعة', 'المنافقون', 'التغابن', 'الطلاق', 'التحريم' => ['number' => 28, 'name' => 'جزء قد سمع'],
            'البقرة' => ['number' => 1, 'name' => 'الجزء الأول'],
            default  => ['number' => 30, 'name' => 'جزء عمّ'], // الافتراضي للبدايات
        };

        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'parent' => [
                'id' => $this->parent?->id,
                'name' => $this->parent?->name,
                'email' => $this->parent?->email,
            ],
            'mosque' => [
                'id' => $this->mosque?->id,
                'name' => $this->mosque?->name,
            ],
            'halaqats' => $this->halaqats->map(fn($h) => [
                'id' => $h->id,
                'name' => $h->name,
            ]),
            'statistics' => [
                'total_absent_days' => $this->total_absent ?? 0,
                'total_present_days' => $this->total_present ?? 0,
                'attendance_rate' => $attendance_rate,
                'last_attendance_at' => $this->last_presence ?? 'لم يتم التسجيل بعد',
            ],

            // 🎯 الحقل الجديد المطلوب رسمياً لحل مشكلة التقدير العام (The Pill)
            'evaluation_summary' => [
                'score_avg' => round($avg_score, 1) . '%',
                'label'     => $label, // يعود بقيم: excellent | very_good | good | acceptable
            ],

            'profile' => [
                'date_of_birth' => $this->date_of_birth,
                'gender' => $this->gender,
                'status' => $this->status,
                'joined_at' => $this->created_at->format('Y-m-d'),

                'progress' => [
                    'juz_number'  => $lastEvaluation ? $juzMapping['number'] : 0,
                    'juz_name'    => $lastEvaluation ? $juzMapping['name'] : 'لم يبدأ بعد',
                    'last_sura'   => $lastSura,
                    'mastery_pct' => round($avg_score, 1) . '%', // نسبة الإتقان مبنية على متوسط درجاته
                ]
            ]

        ];
    }
}
