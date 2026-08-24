<?php

namespace Modules\Volunteer\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\Mosque\Models\Mosque;
use Modules\User\Models\Role;
use Modules\User\Models\User;
use Modules\Volunteer\Enums\ApplicationStatus;
use Modules\Volunteer\Enums\OpportunityStatus;
use Modules\Volunteer\Enums\TaskStatus;
use Modules\Volunteer\Models\VolunteerApplication;
use Modules\Volunteer\Models\VolunteerCertificate;
use Modules\Volunteer\Models\VolunteerLog;
use Modules\Volunteer\Models\VolunteerOpportunity;
use Modules\Volunteer\Models\VolunteerTask;

class VolunteerSeeder extends Seeder
{
    private const OPPORTUNITIES_PER_MOSQUE = 2;

    private const VOLUNTEER_NAMES = [
        'أحمد العلي', 'محمد الحسن', 'خالد الدين', 'عمر فارس', 'يوسف الشامي',
        'عبد الله نور', 'زيد القاسم', 'سلمان الرفاعي', 'بلال الزهراني', 'طارق الحلبي',
        'فادي العلي', 'مالك السباعي', 'سامر يوسف', 'كنان الخطيب', 'وليد العمر',
        'رامي النجار', 'هاني الشامي', 'نور الدين', 'ضياء المنصور', 'باسل الكردي',
    ];

    private const OPPORTUNITY_TEMPLATES = [
        ['تنظيف وترتيب المسجد', 'حملة تطوعية لنظافة المصلى الرئيسي ودورات المياه وترتيب السجاد بالتعاون مع أهالي الحي.'],
        ['استقبال المصلين في صلاة التراويح', 'تنظيم دخول المصلين وتوزيع المصاحف وتقديم المياه الباردة بعد الصلاة.'],
        ['حملة توزيع إفطار صائم', 'تجهيز وتوزيع وجبات إفطار على المارة والفقراء بالتنسيق مع لجنة المسجد.'],
        ['تنظيم حفل القرآن السنوي', 'الإشراف على حفل تكريم حفظة القرآن الكريم وتجهيز المسرح والضيافة.'],
        ['صيانة وتجهيز قاعة الدروس', 'طلاء وتعقيم قاعة الدروس وتجهيزها للمحاضرات والدروس اليومية.'],
        ['الإشراف على حلقات تحفيظ القرآن', 'متابعة حضور الطلاب وتنظيم الجداول في حلقات تحفيظ القرآن الكريم.'],
        ['حملة تشجير ساحة المسجد', 'زراعة أشجار وورود في ساحة المسجد الخارجية وتنظيف المكان.'],
        ['تجهيز مخيم الأيتام', 'تجهيز وتنظيم فعاليات ترفيهية للأيتام بالتعاون مع الجمعيات الخيرية.'],
    ];

    private const TASK_TEMPLATES = [
        'تنظيف المصلى الرئيسي',
        'ترتيب السجاد وتعقيمه',
        'تعقيم دورات المياه',
        'توزيع وجبات الإفطار',
        'تنظيم دخول المصلين',
        'إعداد القاعة للدروس',
        'تجهيز الكراسي والطاولات',
        'زراعة الأشجار في الساحة',
    ];

    private const EVALUATIONS = ['ممتاز', 'جيد جداً', 'جيد', 'أداء متميز'];

    public function run(): void
    {
        $mosques = Mosque::all();

        if ($mosques->isEmpty()) {
            $this->command?->warn('لا توجد مساجد بعد — تجاوز بذر المتطوعين. قم ببذر وحدة المساجد أولاً.');

            return;
        }

        $volunteers = $this->ensureVolunteers();

        foreach ($mosques as $mosque) {
            // تجنب التكرار عند إعادة البذر: تجاوز المسجد إن كان لديه فرص سابقة
            if (VolunteerOpportunity::where('mosque_id', $mosque->id)->exists()) {
                continue;
            }

            for ($i = 0; $i < self::OPPORTUNITIES_PER_MOSQUE; $i++) {
                $opportunity = $this->createOpportunity($mosque, $i);
                $this->seedApplicationsForOpportunity($opportunity, $volunteers);
            }
        }

        $this->command?->info('تم إنشاء بيانات المتطوعين والفرص التطوعية لـ ' . $mosques->count() . ' مسجداً في دمشق.');
    }

    /**
     * تأكد من وجود مجموعة متطوعين (عربية/سورية) وأعدها.
     *
     * @return Collection<int, User>
     */
    private function ensureVolunteers(): Collection
    {
        // التأكد من وجود دور "المتطوع" قبل ربطه بالمستخدمين
        $volunteerRole = Role::firstOrCreate(
            ['name' => 'volunteer'],
            ['display_name' => 'المتطوع']
        );

        $volunteers = User::whereHas('roles', fn($q) => $q->where('name', 'volunteer'))->get();

        if ($volunteers->count() >= 12) {
            return $volunteers;
        }

        // حلقة محدودة بـ 12 متطوعاً لتفادي التكرار اللانهائي عند وجود مستخدمين بنفس البريد
        for ($i = 1; $i <= 12; $i++) {
            if ($volunteers->count() >= 12) {
                break;
            }

            $fullName = self::VOLUNTEER_NAMES[($i - 1) % count(self::VOLUNTEER_NAMES)];
            $email = 'volunteer' . $i . '@mms.test';

            $user = User::where('email', $email)->first();

            if (!$user) {
                $parts = explode(' ', $fullName, 2);
                $user = User::create([
                    'first_name' => $parts[0],
                    'last_name' => $parts[1] ?? '',
                    'name' => $fullName,
                    'email' => $email,
                    'phone' => '09' . str_pad((string) ($i * 137123), 8, '0', STR_PAD_LEFT),
                    'password' => bcrypt('password'),
                    'status' => 'active',
                ]);
            }

            // ربط المستخدم الفعلي بدور المتطوع بشكل صريح
            if (!$user->roles()->where('role_id', $volunteerRole->id)->exists()) {
                $user->roles()->attach($volunteerRole->id);
            }

            // تجنب إضافة نفس المستخدم أكثر من مرة للمجموعة
            if (!$volunteers->contains('id', $user->id)) {
                $volunteers->push($user);
            }
        }

        return $volunteers;
    }

    private function createOpportunity(Mosque $mosque, int $index): VolunteerOpportunity
    {
        $status = $index % 2 === 0 ? OpportunityStatus::Open : OpportunityStatus::Closed;

        $template = self::OPPORTUNITY_TEMPLATES[($mosque->id + $index) % count(self::OPPORTUNITY_TEMPLATES)];

        $startDate = $status === OpportunityStatus::Closed
            ? Carbon::now()->subMonths(2)->addDays($index * 3)
            : Carbon::now()->subDays(10)->addDays($index * 5);

        $endDate = (clone $startDate)->addDays(rand(5, 15));

        return VolunteerOpportunity::create([
            'mosque_id' => $mosque->id,
            'title' => $template[0],
            'description' => $template[1],
            'required_volunteers' => rand(3, 8),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status,
        ]);
    }

    private function seedApplicationsForOpportunity(VolunteerOpportunity $opportunity, Collection $volunteers): void
    {
        $applicantCount = min($opportunity->required_volunteers, $volunteers->count());
        $applicants = $volunteers->take($applicantCount);

        $approvedSoFar = 0;

        foreach ($applicants as $volunteer) {
            $status = $approvedSoFar < $opportunity->required_volunteers
                ? ApplicationStatus::Approved
                : fake()->randomElement([ApplicationStatus::Pending, ApplicationStatus::Rejected]);

            if ($status === ApplicationStatus::Approved) {
                $approvedSoFar++;
            }

            $application = VolunteerApplication::firstOrCreate(
                [
                    'opportunity_id' => $opportunity->id,
                    'volunteer_id' => $volunteer->id,
                ],
                [
                    'status' => $status,
                ]
            );

            if ($status === ApplicationStatus::Approved) {
                $this->seedTasksForApplication($application, $opportunity);
            }
        }
    }

    private function seedTasksForApplication(VolunteerApplication $application, VolunteerOpportunity $opportunity): void
    {
        $opportunityClosed = $opportunity->status === OpportunityStatus::Closed;
        $taskCount = rand(1, 2);

        for ($i = 0; $i < $taskCount; $i++) {
            $description = self::TASK_TEMPLATES[($opportunity->id + $application->id + $i) % count(self::TASK_TEMPLATES)];
            $status = $opportunityClosed ? TaskStatus::Completed : fake()->randomElement([TaskStatus::Assigned, TaskStatus::Completed]);

            $task = VolunteerTask::firstOrCreate(
                [
                    'application_id' => $application->id,
                    'task_description' => $description,
                ],
                [
                    'opportunity_id' => $opportunity->id,
                    'status' => $status,
                ]
            );

            // سجل ساعات التطوع لكل مهمة مكتملة
            if ($status === TaskStatus::Completed) {
                VolunteerLog::firstOrCreate(
                    [
                        'volunteer_id' => $application->volunteer_id,
                        'opportunity_id' => $opportunity->id,
                        'notes' => 'إنجاز مهمة: ' . $description,
                    ],
                    [
                        'logged_hours' => rand(3, 6),
                        'manager_evaluation' => self::EVALUATIONS[($opportunity->id + $i) % count(self::EVALUATIONS)],
                    ]
                );
            }
        }

            // إصدار شهادة حقيقية (ملف PDF مرفوع فعلياً على Supabase) عند إغلاق
            // الفرصة وكذا المهام منجزة — بحيث تصبح قابلة للتنزيل مباشرةً.
            if ($opportunityClosed) {
                $this->issueRealCertificate($application->volunteer_id, $opportunity->id);
            }
    }

    /**
     * إصدار شهادة حقيقية لمتطوع على فرصة مغلقة عبر خدمة التقييم (توليد PDF + رفع
     * على Supabase). نتجاوز التكرار عند إعادة البذر، ونكمل بذر باقي البيانات حتى
     * لو فشل رفع الـ PDF (مثلاً بسبب غياب إعدادات Supabase).
     */
    private function issueRealCertificate(int $volunteerId, int $opportunityId): void
    {
        if (VolunteerCertificate::where('volunteer_id', $volunteerId)
            ->where('opportunity_id', $opportunityId)
            ->exists()
        ) {
            return;
        }

        try {
            $service = app(\Modules\Volunteer\Services\VolunteerEvaluationService::class);
            $service->issueCertificate($volunteerId, $opportunityId);

            $this->command?->info("✔ تم إصدار شهادة حقيقية للمتطوع {$volunteerId} في الفرصة {$opportunityId}");
        } catch (\Throwable $e) {
            $this->command?->warn(
                "⚠ تعذّر إصدار الشهادة للمتطوع {$volunteerId} في الفرصة {$opportunityId}: {$e->getMessage()}"
            );
        }
    }
}
