<?php

namespace Modules\Community\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Modules\Mosque\Models\Mosque;
use Modules\Mosque\Models\MosqueSpace;

class DawahProgramSeeder extends Seeder
{
    /**
     * أسماء البرامج الدعوية الواقعية.
     */
    private array $programs = [
        [
            'program_name' => 'درس تفسير القرآن الكريم',
            'description' => 'درس أسبوعي يتناول تفسير آيات القرآن الكريم وبيان معانيها وأحكامها بطريقة مبسطة.',
            'type' => 'lecture',
            'presenter' => 'الشيخ محمد عبد الرحمن',
            'level' => 'beginner',
        ],
        [
            'program_name' => 'دورة أحكام التجويد',
            'description' => 'دورة تعليمية لتعلم أحكام التجويد الأساسية وتطبيقها أثناء تلاوة القرآن الكريم.',
            'type' => 'course',
            'presenter' => 'الشيخ أحمد الخطيب',
            'level' => 'beginner',
        ],
        [
            'program_name' => 'دورة حفظ القرآن الكريم',
            'description' => 'برنامج مخصص للراغبين في حفظ القرآن الكريم مع المتابعة والمراجعة الدورية.',
            'type' => 'course',
            'presenter' => 'الشيخ عبد الرحمن الحسن',
            'level' => 'intermediate',
        ],
        [
            'program_name' => 'مسابقة القرآن الكريم',
            'description' => 'مسابقة قرآنية تهدف إلى تشجيع الأطفال والشباب على حفظ كتاب الله تعالى وإتقانه.',
            'type' => 'competition',
            'presenter' => 'إدارة المسجد',
            'level' => 'beginner',
        ],
        [
            'program_name' => 'مجلس تدبر القرآن',
            'description' => 'مجلس إيماني لتدبر آيات القرآن الكريم واستخلاص الدروس والفوائد العملية منها.',
            'type' => 'lecture',
            'presenter' => 'الشيخ يوسف الدمشقي',
            'level' => 'intermediate',
        ],
        [
            'program_name' => 'فقه العبادات',
            'description' => 'دروس في فقه الصلاة والصيام والزكاة وسائر العبادات وفق منهج مبسط.',
            'type' => 'course',
            'presenter' => 'الشيخ خالد الشامي',
            'level' => 'intermediate',
        ],
        [
            'program_name' => 'محاضرة تربية الأبناء',
            'description' => 'محاضرة توعوية حول أساليب التربية الإسلامية وبناء شخصية الأبناء.',
            'type' => 'lecture',
            'presenter' => 'الشيخ سامر النعسان',
            'level' => 'beginner',
        ],
        [
            'program_name' => 'برنامج الشباب الإيماني',
            'description' => 'برنامج شبابي يجمع بين التوجيه الديني والأنشطة الثقافية والتربوية.',
            'type' => 'other',
            'presenter' => 'فريق الدعوة والإرشاد',
            'level' => 'beginner',
        ],
        [
            'program_name' => 'دورة السيرة النبوية',
            'description' => 'دورة تتناول أحداث السيرة النبوية وأهم الدروس والعبر المستفادة منها.',
            'type' => 'course',
            'presenter' => 'الشيخ عمر القادري',
            'level' => 'intermediate',
        ],
        [
            'program_name' => 'محاضرة أخلاق المسلم',
            'description' => 'محاضرة حول الأخلاق الإسلامية وأثرها في بناء المجتمع والأسرة.',
            'type' => 'lecture',
            'presenter' => 'الشيخ حسن الرفاعي',
            'level' => 'beginner',
        ],
    ];

    /**
     * صور تجريبية.
     *
     * يمكن استبدالها لاحقاً بمسارات صور حقيقية في Supabase.
     */
    private array $images = [
        'dawah/programs/quran.jpg',
        'dawah/programs/tajweed.jpg',
        'dawah/programs/lecture.jpg',
        'dawah/programs/islamic-course.jpg',
    ];

    public function run(): void
    {
        DB::beginTransaction();

        try {

            $this->command->info('');
            $this->command->info('🔄 بدء إنشاء البرامج الدعوية ومواعيدها...');
            $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

            // =========================================================
            // 1. جلب المساجد
            // =========================================================

            $mosques = Mosque::query()
                ->orderBy('id')
                ->get();

            if ($mosques->isEmpty()) {
                throw new \Exception(
                    '❌ لا توجد مساجد في قاعدة البيانات. شغّل MosqueSeeder أولاً.'
                );
            }

            $this->command->info(
                '🕌 تم العثور على ' . $mosques->count() . ' مسجد.'
            );

            // =========================================================
            // 2. التأكد من وجود جدول المساحات
            // =========================================================

            if (!Schema::hasTable('mosque_spaces')) {
                throw new \Exception(
                    '❌ جدول mosque_spaces غير موجود. شغّل migration الخاصة به أولاً.'
                );
            }

            if (!Schema::hasTable('dawah_programs')) {
                throw new \Exception(
                    '❌ جدول dawah_programs غير موجود. شغّل migration الخاصة به أولاً.'
                );
            }

            if (!Schema::hasTable('program_schedules')) {
                throw new \Exception(
                    '❌ جدول program_schedules غير موجود. شغّل migration الخاصة به أولاً.'
                );
            }

            // =========================================================
            // 3. إنشاء البرامج لكل مسجد
            // =========================================================

            $totalPrograms = 0;
            $totalSchedules = 0;

            foreach ($mosques as $mosque) {

                $this->command->info('');
                $this->command->info(
                    "🕌 المسجد: {$mosque->name} (ID: {$mosque->id})"
                );

                // -----------------------------------------------------
                // جلب المساحات التابعة للمسجد
                // -----------------------------------------------------

                $spaces = MosqueSpace::where(
                    'mosque_id',
                    $mosque->id
                )
                    ->orderBy('id')
                    ->get();

                // إذا لم توجد مساحة، ننشئ واحدة تلقائياً.
                if ($spaces->isEmpty()) {

                    $space = MosqueSpace::create([
                        'mosque_id' => $mosque->id,
                        'name' => 'قاعة الأنشطة',
                        'capacity' => 50,
                    ]);

                    $spaces = collect([$space]);

                    $this->command->info(
                        '   ➕ تم إنشاء مساحة افتراضية: قاعة الأنشطة'
                    );
                }

                $this->command->info(
                    '   🏢 المساحات المتاحة: ' . $spaces->count()
                );

                // -----------------------------------------------------
                // إنشاء 5 برامج لكل مسجد
                // -----------------------------------------------------

                $selectedPrograms = collect($this->programs)
                    ->shuffle()
                    ->take(5)
                    ->values();

                foreach ($selectedPrograms as $programIndex => $programData) {

                    $space = $spaces[
                    $programIndex % $spaces->count()
                    ];

                    /*
                     * نستخدم اسم المسجد + اسم البرنامج
                     * حتى تكون البيانات واضحة عند التجربة.
                     */

                    $programName = $programData['program_name'];

                    // -------------------------------------------------
                    // البحث عن البرنامج مسبقاً
                    // -------------------------------------------------

                    $existingProgram = DB::table('dawah_programs')
                        ->where('mosque_id', $mosque->id)
                        ->where('program_name', $programName)
                        ->first();

                    if ($existingProgram) {

                        $programId = $existingProgram->id;

                        // تحديث البيانات
                        DB::table('dawah_programs')
                            ->where('id', $programId)
                            ->update([
                                'space_id' => $space->id,
                                'description' => $programData['description'],
                                'type' => $programData['type'],
                                'presenter' => $programData['presenter'],
                                'level' => $programData['level'],
                                'is_featured' => $programIndex === 0,
                                'status' => 'active',
                                'updated_at' => now(),
                            ]);

                    } else {

                        $programId = DB::table('dawah_programs')
                            ->insertGetId([
                                'mosque_id' => $mosque->id,
                                'space_id' => $space->id,
                                'program_name' => $programName,
                                'description' => $programData['description'],
                                'type' => $programData['type'],
                                'image' => $this->images[
                                $programIndex % count($this->images)
                                ],
                                'presenter' => $programData['presenter'],
                                'presenter_image' => null,

                                // Boolean حقيقي لـ PostgreSQL


                                'status' => 'active',
                                'level' => $programData['level'],

                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                    }

                    $totalPrograms++;

                    $this->command->info(
                        "   📖 {$programName}"
                    );

                    $this->command->info(
                        "      👤 المحاضر: {$programData['presenter']}"
                    );

                    $this->command->info(
                        "      🏢 المكان: {$space->name}"
                    );

                    // =================================================
                    // 4. إنشاء مواعيد البرنامج
                    // =================================================

                    $totalSchedules += $this->createSchedules(
                        $programId,
                        $programData['program_name']
                    );
                }
            }

            // =========================================================
            // 5. Commit
            // =========================================================

            DB::commit();

            $this->command->info('');
            $this->command->info(
                '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━'
            );

            $this->command->info(
                '✅ تم تنفيذ DawahProgramSeeder بنجاح.'
            );

            $this->command->info(
                '🕌 المساجد: ' . $mosques->count()
            );

            $this->command->info(
                '📖 البرامج: ' . $totalPrograms
            );

            $this->command->info(
                '📅 المواعيد: ' . $totalSchedules
            );

            $this->command->info(
                '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━'
            );

        } catch (\Throwable $e) {

            DB::rollBack();

            $this->command->error('');
            $this->command->error(
                '❌ حدث خطأ أثناء إنشاء البرامج الدعوية.'
            );

            $this->command->error(
                '📍 الرسالة: ' . $e->getMessage()
            );

            $this->command->error(
                '📍 الملف: ' . $e->getFile()
            );

            $this->command->error(
                '📍 السطر: ' . $e->getLine()
            );

            throw $e;
        }
    }

    /**
     * إنشاء عدة مواعيد للبرنامج.
     */
    private function createSchedules(
        int $programId,
        string $programName
    ): int {

        $scheduleTemplates = [
            [
                'days' => 0,
                'start' => '17:00:00',
                'end' => '18:30:00',
            ],
            [
                'days' => 2,
                'start' => '18:00:00',
                'end' => '19:30:00',
            ],
            [
                'days' => 4,
                'start' => '19:00:00',
                'end' => '20:30:00',
            ],
        ];

        $created = 0;

        foreach ($scheduleTemplates as $index => $template) {

            $date = Carbon::today()
                ->addDays($template['days'] + 1);

            /*
             * منع التكرار إذا تم تشغيل Seeder أكثر من مرة.
             */

            $exists = DB::table('program_schedules')
                ->where('dawah_program_id', $programId)
                ->whereDate('date', $date->toDateString())
                ->where('start_time', $template['start'])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('program_schedules')->insert([
                'dawah_program_id' => $programId,

                'title' => $this->getScheduleTitle(
                    $programName,
                    $index
                ),

                'notes' => $this->getScheduleNotes(
                    $programName
                ),

                'date' => $date->toDateString(),

                'start_time' => $template['start'],

                'end_time' => $template['end'],

                'created_at' => now(),

                'updated_at' => now(),
            ]);

            $created++;
        }

        return $created;
    }

    /**
     * عنوان الموعد.
     */
    private function getScheduleTitle(
        string $programName,
        int $index
    ): string {

        return match ($index) {
            0 => "اللقاء الأول - {$programName}",
            1 => "اللقاء الثاني - {$programName}",
            default => "اللقاء الثالث - {$programName}",
        };
    }

    /**
     * ملاحظات الموعد.
     */
    private function getScheduleNotes(
        string $programName
    ): string {

        return match (true) {

            str_contains($programName, 'تجويد') =>
            'يرجى إحضار المصحف الشخصي والالتزام بالحضور في الموعد المحدد.',

            str_contains($programName, 'حفظ') =>
            'يُرجى مراجعة المقرر السابق قبل حضور الحلقة.',

            str_contains($programName, 'مسابقة') =>
            'التسجيل مفتوح لجميع الفئات العمرية وفق شروط المسابقة.',

            str_contains($programName, 'شباب') =>
            'البرنامج مخصص للشباب ويتضمن فقرات إيمانية وتربوية.',

            default =>
            'يرجى الحضور قبل بداية البرنامج بعشر دقائق.',
        };
    }
}
