<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insertOrIgnore([
            'role_id' => 1,
            'role_name' => 'Sub Admin',
            'role_description' => 'Sub administrator with limited permissions',
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
