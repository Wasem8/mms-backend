<?php

namespace Modules\MaintenanceRequest\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\MaintenanceRequest\Models\Maintenance;
use Modules\MaintenanceRequest\Models\MaintenanceFile;
use Modules\User\Models\User;

class MaintenanceFileSeeder extends Seeder
{
    public function run(): void
    {
        $requester = User::whereHas('roles', fn($q) => $q->where('name', 'mosque_manager'))
            ->first()
            ?? User::first();

        if (!$requester) {
            $this->command->warn('لا يوجد مستخدم لربط ملفات الصيانة، تم تخطي البذر.');

            return;
        }

        // أسماء ملفات توثيقية حسب التصنيف (عربي)
        $fileNamesByCategory = [
            'electrical' => ['صورة-الأسلاك-الكهربائية.jpg', 'تقرير-فني-الكهرباء.pdf'],
            'plumbing'   => ['صورة-تسريب-المياه.jpg', 'تقرير-فني-السباكة.pdf'],
            'carpentry'  => ['صورة-الباب-الخشبي.jpg', 'عرض-سعر-النجارة.pdf'],
            'cleaning'   => ['صورة-أعمال-التنظيف.jpg'],
            'other'      => ['صورة-الموقع.jpg', 'تقرير-معاينة.pdf'],
        ];

        $count = 0;

        foreach (Maintenance::all() as $maintenance) {
            // تجنب التكرار عند إعادة البذر
            if (MaintenanceFile::where('maintenance_id', $maintenance->id)->exists()) {
                continue;
            }

            $names = $fileNamesByCategory[$maintenance->category] ?? ['صورة-الموقع.jpg'];

            foreach ($names as $index => $fileName) {
                $isPdf = str_ends_with($fileName, '.pdf');
                $extension = $isPdf ? 'pdf' : 'jpeg';

                MaintenanceFile::create([
                    'maintenance_id' => $maintenance->id,
                    'file_name' => $fileName,
                    'file_path' => 'maintenance/' . $maintenance->maintenance_number . '-' . ($index + 1) . '.' . $extension,
                    'file_type' => $isPdf ? 'application/pdf' : 'image/jpeg',
                    'file_size' => $isPdf ? 180000 + $maintenance->id * 500 : 250000 + $maintenance->id * 1000,
                ]);

                $count++;
            }

            // تحديث حالة طلب الصيانة لطلب الملفات (يُخزَّن كـ boolean أصلي لـ PostgreSQL)
            DB::table('maintenances')
                ->where('id', $maintenance->id)
                ->update([
                    'files_requested' => DB::raw('true'),
                    'files_requested_by' => $requester->id,
                    'files_requested_at' => now(),
                    'files_request_note' => 'يرجى إرفاق صور توثيقية للمشكلة قبل الشروع بالصيانة',
                ]);
        }

        $this->command->info('تم إنشاء/تحديث ' . $count . ' ملف صيانة وربطه بطلبات الصيانة.');
    }
}
