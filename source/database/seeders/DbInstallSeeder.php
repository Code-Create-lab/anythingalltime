<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DbInstallSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Use the comprehensive seeder that contains ALL data from database.sql
        // This guarantees 100% no data loss during migration
        $this->call([
            ComprehensiveDataSeeder::class,
        ]);

        // Note: Individual seeders are still available for development:
        // SystemConfigSeeder, AdminSeeder, RolesSeeder, SettingsSeeder,
        // ContentSeeder, CancellationReasonSeeder, LookupDataSeeder
    }
}
