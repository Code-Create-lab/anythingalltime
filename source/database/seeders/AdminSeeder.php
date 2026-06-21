<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Legacy schema: id,name,email,password,admin_image,remember_token,role_id,role_name
        DB::table('admin')->insertOrIgnore([
            'id' => 1,
            'name' => 'GoGrocer Admin',
            'email' => 'admin@demo.com',
            'password' => '$2y$10$VD8DroA2J31Zfsvhef3zUO7dwBeLlXMmmggstTzkzsZ6WdgtBC6UK',
            'admin_image' => 'images/admin/profile/07-04-20/070420120712pm-604a0cadf94914c7ee6c6e552e9b4487-curved-check-mark-circle-icon-by-vexels.png',
            'role_id' => 1,
            'role_name' => 'Admin',
        ]);
    }
}
