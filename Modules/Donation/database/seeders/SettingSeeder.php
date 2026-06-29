<?php

namespace Modules\Donation\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Donation\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $this->call([]);
        $defaults = [
            [
                'key'         => 'usd_to_syp_rate',
                'value'       => '14000',
            ],
        ];

        foreach ($defaults as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value'       => $setting['value'],
                ],
            );
        }
    }
}
