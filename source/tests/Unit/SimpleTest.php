<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SimpleTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_connection_works()
    {
        // Test that we can connect to the database
        $result = DB::select('SELECT 1 as test');
        $this->assertEquals(1, $result[0]->test);
    }

    public function test_database_name_is_correct()
    {
        // Test that we're using the correct database connection
        $driver = DB::getDriverName();
        // For CI, we use SQLite, for local development, MySQL
        $this->assertContains($driver, ['mysql', 'sqlite']);
    }

    public function test_store_table_exists()
    {
        // Test that the store table exists (database agnostic)
        if (DB::getDriverName() === 'sqlite') {
            $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='store'");
        } else {
            $tables = DB::select("SHOW TABLES LIKE 'store'");
        }
        $this->assertNotEmpty($tables);
    }

    public function test_database_transactions_work()
    {
        // Test that database transactions work (for test isolation)
        $originalCount = DB::table('store')->count();

        // This should be rolled back after the test
        DB::table('store')->insert([
            'store_name' => 'Test Store for Transaction',
            'employee_name' => 'Test Employee',
            'phone_number' => '1234567890',
            'email' => 'test@example.com',
            'password' => 'password',
            'city' => 'Test City',
            'city_id' => 1,
            'address' => 'Test Address',
            'lat' => '0.0',
            'lng' => '0.0',
            'del_range' => 10.0,
            'store_status' => 1,
            'store_opening_time' => '09:00',
            'store_closing_time' => '18:00',
            'time_interval' => 30,
        ]);

        $newCount = DB::table('store')->count();
        $this->assertEquals($originalCount + 1, $newCount);

        // The transaction will be rolled back automatically after the test
    }
}
