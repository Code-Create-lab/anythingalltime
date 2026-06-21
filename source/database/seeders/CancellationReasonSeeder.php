<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CancellationReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cancellationReasons = [
            ['res_id' => 6, 'reason' => 'TAKING TO MUCH TIME'],
            ['res_id' => 7, 'reason' => 'PRICE IS DIFFERENT FROM OTHER STORE'],
            ['res_id' => 8, 'reason' => 'Changed My Mind.'],
            ['res_id' => 9, 'reason' => 'NOT INTERESTED'],
            ['res_id' => 10, 'reason' => 'NOT INTERESTED'],
        ];

        DB::table('cancel_for')->insertOrIgnore($cancellationReasons);
    }
}
