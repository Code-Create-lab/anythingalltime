<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LookupDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // ID Types
        DB::table('id_types')->insertOrIgnore([
            'type_id' => 1,
            'name' => 'Aadhar Card',
        ]);
    }
}
