<?php

namespace Modules\Community\Database\Seeders;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\User\Models\User as ModelsUser;

class SermonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. التأكد من وجود مستخدمين (مدير مسجد ومدير منطقة)
        $mosqueManager = ModelsUser::firstOrCreate(
            ['email' => 'mosque_manager@example.com'],
            [
                'name' => 'الشيخ أحمد (مدير المسجد)',
                'password' => bcrypt('password'),
            ]
        );

        $regionManager = ModelsUser::firstOrCreate(
            ['email' => 'region_manager@example.com'],
            [
                'name' => 'الدكتور عبد الله (مدير المنطقة)',
                'password' => bcrypt('password'),
            ]
        );

        // -------------------------------------------------------------
        // 2. إنشاء خطب قيد الانتظار (Pending Sermons)
        // -------------------------------------------------------------
        $pendingSermonsData = [
            [
                'title' => 'أهمية البر بالوالدين وأثره في الحياة',
                'content' => 'نص الخطبة يتحدث عن فضل الوالدين وطاعتهما والإحسان إليهما في الشريعة الإسلامية...',
                'speaker_name' => 'الشيخ محمد علي',
                'sermon_date' => Carbon::now()->addDays(5)->toDateString(),
                'status' => 'Pending',
                'notes' => 'في انتظار مراجعة مدير المنطقة',
                'mosque_manager_id' => $mosqueManager->id,
                'region_manager_id' => $regionManager->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'أمانة الكلمة وحفظ اللسان عن الغيبة',
                'content' => 'خطبة تتناول خطورة الشائعات والغيبة وأثر ضبط اللسان على تماسك المجتمع...',
                'speaker_name' => 'الشيخ يوسف إبراهيم',
                'sermon_date' => Carbon::now()->addDays(12)->toDateString(),
                'status' => 'Pending',
                'notes' => 'مقترحة لجمعة قادمة',
                'mosque_manager_id' => $mosqueManager->id,
                'region_manager_id' => $regionManager->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($pendingSermonsData as $sermon) {
            $sermonId = DB::table('sermons')->insertGetId($sermon);

            // إضافة مرفق تجريبي للخطبة
            DB::table('sermon_attachments')->insert([
                'sermon_id' => $sermonId,
                'file_path' => 'sermons/pdf/sample_' . $sermonId . '.pdf',
                'file_type' => 'pdf',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // -------------------------------------------------------------
        // 3. إنشاء خطب سابقة وتحديدها للجمع الماضية (Sermon Selections)
        // -------------------------------------------------------------
        $pastSermonsData = [
            [
                'title' => 'التكافل الاجتماعي في الإسلام',
                'content' => 'نص الخطبة حول الزكاة والصدقات ودورهما في تقوية العلاقات بين أفراد المجتمع...',
                'speaker_name' => 'الشيخ عمر الخالد',
                'status' => 'Archived',
            ],
            [
                'title' => 'فضل أداء الصلاة في وقتها مع الجماعة',
                'content' => 'نص الخطبة عن أهمية المحافظة على صلاة الجماعة وآثارها الروحية والاجتماعية...',
                'speaker_name' => 'الشيخ حسن محمود',
                'status' => 'Archived',
            ],
            [
                'title' => 'الصبر عند البلاء وحسن الظن بالله',
                'content' => 'خطبة توجيهية عن الصبر والثبات والطمأنينة عند مواجهة الصعاب...',
                'speaker_name' => 'الشيخ عبد الرحمن السعدي',
                'status' => 'Archived',
            ],
            [
                'title' => 'حق الجار في الشريعة الإسلامية',
                'content' => 'تناول الوصايا النبوية بالجار وأشكال الإحسان إليه وكف الأذى عنه...',
                'speaker_name' => 'الشيخ خالد النجار',
                'status' => 'Archived',
            ],
        ];

        // جلب الجمعة الماضية ثم التراجع أسبوعًا بعد كل دورة
        $previousFriday = Carbon::parse('last friday');

        foreach ($pastSermonsData as $index => $data) {
            $fridayDate = $previousFriday->copy()->subWeeks($index)->toDateString();

            // أ) إضافة الخطبة إلى جدول الـ sermons
            $sermonId = DB::table('sermons')->insertGetId([
                'title' => $data['title'],
                'content' => $data['content'],
                'speaker_name' => $data['speaker_name'],
                'sermon_date' => $fridayDate,
                'status' => $data['status'],
                'notes' => 'تم القاؤها بنجاح',
                'mosque_manager_id' => $mosqueManager->id,
                'region_manager_id' => $regionManager->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // ب) إضافة اختيار الخطبة للجمعة السابقة في جدول sermon_selections
            DB::table('sermon_selections')->insert([
                'mosque_manager_id' => $mosqueManager->id,
                'sermon_id' => $sermonId,
                'friday_date' => $fridayDate,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // ج) إضافة مرفق Word/PDF لكل خطبة سابقة
            DB::table('sermon_attachments')->insert([
                'sermon_id' => $sermonId,
                'file_path' => 'sermons/docx/archived_' . $sermonId . '.docx',
                'file_type' => 'docx',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
