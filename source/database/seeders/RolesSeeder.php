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
        // Legacy schema: role_id, role_name + per-module permission flags (no description/status/timestamps)
        DB::table('roles')->insertOrIgnore([
            'role_id' => 1,
            'role_name' => 'Sub Admin',
        ]);
    }
}
