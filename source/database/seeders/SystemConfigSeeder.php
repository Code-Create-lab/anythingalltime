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

        // Country codes
        DB::table('country_code')->insertOrIgnore([
            'id' => 1,
            'country_code' => '91',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Tax types - Table doesn't exist in current migration structure
        // DB::table('tax_types')->insertOrIgnore([
        //     'id' => 3,
        //     'name' => 'GST',
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);

        // Membership plans
        DB::table('membership_plan')->insertOrIgnore([
            'plan_id' => 1,
            'plan_name' => 'Premium',
            'reward' => 1.0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
