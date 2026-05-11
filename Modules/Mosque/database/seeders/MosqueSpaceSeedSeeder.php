<?php

namespace Modules\Mosque\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Mosque\Models\MosqueSpace;

class MosqueSpaceSeedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('mosque_spaces')->insert([
            [
                'mosque_id'=>1,
                'name'=>'القاعة الأولى',
                'capacity'=>50,
            ],
            [
                'mosque_id' => 1,
                'name' => 'القاعة الثانية',
                'capacity' => 50,
            ],
            [
                'mosque_id' => 1,
                'name' => 'قاعة تحفيظ القران',
                'capacity' => 40,
            ],
        ]);
    }
}
