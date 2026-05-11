<?php

namespace Modules\Mosque\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FacilitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('facilities')->insert([
            ['name' => 'موقف سيارات', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'مصلى النساء', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'مكتبة إسلامية', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'دورات مياه', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'مصلى كبار السن', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'مصلى ذوي الاحتياجات الخاصة', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'خدمة الواي فاي', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('facility_mosque')->insert([
            ['mosque_id' => 1, 'facility_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['mosque_id' => 1, 'facility_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['mosque_id' => 1, 'facility_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['mosque_id' => 2, 'facility_id' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['mosque_id' => 2, 'facility_id' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['mosque_id' => 3, 'facility_id' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['mosque_id' => 3, 'facility_id' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['mosque_id' => 4, 'facility_id' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['mosque_id' => 4, 'facility_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['mosque_id' => 4, 'facility_id' => 2, 'created_at' => now(), 'updated_at' => now()]
        ]);
    }
}
