<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Currency settings
        DB::table('currency')->insertOrIgnore([
            'id' => 1,
            'currency_name' => 'INR',
            'currency_sign' => 'Rs',
            'currency_symbol' => '₹',
            'currency_code' => 'INR',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Country codes (legacy schema: code_id PK, no timestamps)
        DB::table('country_code')->updateOrInsert(
            ['country_code' => '91'],
            []
        );

        // Tax types - Table doesn't exist in current migration structure
        // DB::table('tax_types')->insertOrIgnore([
        //     'id' => 3,
        //     'name' => 'GST',
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);

        // Membership plans (legacy schema: no timestamps)
        DB::table('membership_plan')->insertOrIgnore([
            'plan_id' => 1,
            'plan_name' => 'Premium',
            'reward' => 1.0,
        ]);
    }
}
