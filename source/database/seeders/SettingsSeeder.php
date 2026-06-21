<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = [
            ['id' => 31, 'name' => 'paypal_active', 'value' => 'No', 'created_at' => '2020-11-18 13:56:42', 'updated_at' => '2021-02-15 16:32:58'],
            ['id' => 32, 'name' => 'paypal_email', 'value' => 'deekhati63@gmail.com', 'created_at' => '2020-11-18 13:56:42', 'updated_at' => '2021-02-08 15:59:27'],
            ['id' => 34, 'name' => 'stripe_active', 'value' => 'No', 'created_at' => '2020-11-18 13:56:42', 'updated_at' => '2021-02-15 16:32:58'],
            ['id' => 35, 'name' => 'stripe_secret_key', 'value' => env('STRIPE_SECRET_KEY', ''), 'created_at' => '2020-11-18 13:56:42', 'updated_at' => '2021-11-19 16:47:13'],
            ['id' => 36, 'name' => 'stripe_publishable_key', 'value' => 'pk_test_c0oc159sTDjBAxK4JOCpPElA00WOC6sWJq', 'created_at' => '2020-11-18 13:56:42', 'updated_at' => '2021-11-19 16:47:13'],
            ['id' => 38, 'name' => 'razorpay_active', 'value' => 'Yes', 'created_at' => '2020-11-18 13:56:42', 'updated_at' => '2021-02-15 16:32:58'],
            ['id' => 39, 'name' => 'razorpay_key_id', 'value' => 'rzp_test_5eJgxBiQclifFX', 'created_at' => '2020-11-18 13:56:42', 'updated_at' => '2021-11-19 16:47:13'],
            ['id' => 40, 'name' => 'razorpay_secret_key', 'value' => env('RAZORPAY_SECRET_KEY', ''), 'created_at' => '2020-11-18 13:56:42', 'updated_at' => '2021-11-19 16:47:13'],
            ['id' => 42, 'name' => 'paystack_active', 'value' => 'No', 'created_at' => '2020-11-18 13:56:42', 'updated_at' => '2021-02-15 16:32:58'],
            ['id' => 43, 'name' => 'paystack_public_key', 'value' => 'dg', 'created_at' => '2020-11-18 13:56:42', 'updated_at' => '2021-11-19 16:47:13'],
            ['id' => 44, 'name' => 'paystack_secret_key', 'value' => 'sdgdgdsg', 'created_at' => '2020-11-18 13:56:42', 'updated_at' => '2021-11-19 16:47:13'],
            ['id' => 61, 'name' => 'paypal_client_id', 'value' => 'efsdgfdhdfhf', 'created_at' => '2021-02-15 16:32:58', 'updated_at' => '2021-11-19 16:47:13'],
            ['id' => 62, 'name' => 'paypal_secret_key', 'value' => 'sdgdhfdhsfhhsf', 'created_at' => '2021-02-15 16:32:58', 'updated_at' => '2021-11-19 16:47:13'],
            ['id' => 63, 'name' => 'stripe_merchant_id', 'value' => 'acct_1HzzheJi3WFPjQpE', 'created_at' => '2021-03-11 15:44:01', 'updated_at' => '2021-11-19 16:47:13'],
        ];

        // Only insert basic settings for now - more complex configuration tables
        // need to be reviewed against current migration schemas
        DB::table('settings')->insertOrIgnore($settings);
    }
}
