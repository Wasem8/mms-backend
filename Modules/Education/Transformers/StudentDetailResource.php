<?php

namespace Modules\Education\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        $total_records = ($this->total_present ?? 0) + ($this->total_absent ?? 0);

        // §5: رقم مجرد بدون %
        $attendance_rate = $total_records > 0
            ? round(($this->total_present / $total_records) * 100, 2)
            : 0;

        $avg_score = $this->evaluations_avg_score ?? 0;

        $label = match (true) {
            $avg_score >= 90 => 'excellent',
            $avg_score >= 80 => 'very_good',
            $avg_score >= 65 => 'good',
            $avg_score >= 50 => 'acceptable',
            default          => 'acceptable',
        };

        $lastEvaluation = $this->evaluations->first();
        $lastSura = $lastEvaluation?->surah_name ?? null; // §5: null عند عدم وجود تقييم

        $juzMapping = match ($lastSura) {
            'النبأ', 'النازعات', 'عبس', 'التكوير', 'الانفطار', 'المطففين', 'الانشقاق', 'البروج', 'الطارق', 'الأعلى', 'الغاشية', 'الفجر', 'البلد', 'الشمس', 'الليل', 'الضحى', 'الشرح', 'التين', 'العلق', 'القدر', 'البينة', 'الزلزلة', 'العاديات', 'القارعة', 'التكاثر', 'العصر', 'الهمزة', 'الفيل', 'قريش', 'الماعون', 'الكوثر', 'الكافرون', 'النصر', 'المسد', 'الإخلاص', 'الفلق', 'الناس' => ['number' => 30, 'name' => 'جزء عمّ'],
            'الملك', 'القلم', 'الحاقة', 'المعارج', 'نوح', 'الجن', 'المزمل', 'المدثر', 'القيامة', 'الإنسان', 'المرسلات' => ['number' => 29, 'name' => 'جزء تبارك'],
            'المجادلة', 'الحشر', 'الممتحنة', 'الصف', 'الجمعة', 'المنافقون', 'التغابن', 'الطلاق', 'التحريم' => ['number' => 28, 'name' => 'جزء قد سمع'],
            'البقرة' => ['number' => 1, 'name' => 'الجزء الأول'],
            default  => $lastEvaluation ? ['number' => 30, 'name' => 'جزء عمّ'] : null,
        };

        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,

            // §3: تم إخراج هذه الحقول إلى المستويات الأولى لتتطابق مع قائمة الطلاب (Top-level)
            'date_of_birth' => $this->date_of_birth,
            'gender' => $this->gender,
            'status' => $this->status,

            'parent' => [
                'id' => $this->parent?->id,
                'name' => $this->parent?->name,
                'email' => $this->parent?->email,
            ],
            'mosque' => [
                'id' => $this->mosque?->id,
                'name' => $this->mosque?->name,
            ],

            // §7: إضافة معلم الحلقة المدمجة
            'halaqats' => $this->halaqats->map(fn($h) => [
                'id' => $h->id,
                'name' => $h->name,
                'teacher' => $h->teacher ? [
                    'id' => $h->teacher->id,
                    'name' => $h->teacher->name,
                ] : null,
            ]),

            'statistics' => [
                'total_absent_days' => $this->total_absent ?? 0,
                'total_present_days' => $this->total_present ?? 0,
                'attendance_rate' => $attendance_rate, // §5: رقم مثل 90.5 بدلاً من "90.5%"
                'last_attendance_at' => $this->last_presence ?? null, // §5: null بدلاً من "لم يتم التسجيل بعد"
            ],

            'evaluation_summary' => [
                'score_avg' => round($avg_score, 1), // §5: رقم مجرد
                'label'     => $label,
            ],

            'profile' => [
                'joined_at' => $this->created_at->format('Y-m-d'),

                'progress' => [
                    'juz_number'  => $juzMapping ? $juzMapping['number'] : null,
                    'juz_name'    => $juzMapping ? $juzMapping['name'] : null, // §5: null
                    'last_sura'   => $lastSura, // §5: null
                    'mastery_pct' => round($avg_score, 1), // §5: رقم مجرد
                ]
            ]
        ];
    }
}
