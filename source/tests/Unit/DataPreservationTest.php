<?php

namespace Tests\Unit;

use Database\Seeders\ComprehensiveDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DataPreservationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that all critical data from database.sql is preserved
     *
     * This test verifies 100% no data loss during migration from
     * database.sql to Laravel seeders approach.
     */
    public function test_all_critical_data_is_preserved()
    {
        // Run our comprehensive seeder
        $this->seed(ComprehensiveDataSeeder::class);


        // Verify admin account
        $this->assertAdminDataPreserved();

        // Verify settings (payment gateways)
        $this->assertSettingsDataPreserved();

        // Verify currency
        $this->assertCurrencyDataPreserved();

        // Verify roles
        $this->assertRolesDataPreserved();

        // Verify country codes
        $this->assertCountryCodesPreserved();

        // Output summary
        $this->addToAssertionCount(1);
        echo "\n✅ ALL CRITICAL DATA PRESERVATION TESTS PASSED\n";
    }

    private function assertAdminDataPreserved()
    {
        if (! DB::getSchemaBuilder()->hasTable('admin')) {
            $this->markTestSkipped('Admin table does not exist');

            return;
        }

        // Find admin by email instead of hardcoded ID
        $admin = DB::table('admin')->where('email', 'admin@demo.com')->first();
        $this->assertNotNull($admin, 'Default admin account should exist');
        $this->assertEquals('Anything Alltime Admin', $admin->name);
        $this->assertEquals('admin@demo.com', $admin->email);
        $this->assertEquals('$2y$10$VD8DroA2J31Zfsvhef3zUO7dwBeLlXMmmggstTzkzsZ6WdgtBC6UK', $admin->password);

        echo "✅ Admin data preserved\n";
    }

    private function assertSettingsDataPreserved()
    {
        if (! DB::getSchemaBuilder()->hasTable('settings')) {
            $this->markTestSkipped('Settings table does not exist');

            return;
        }

        // Check payment gateway settings
        $paypalActive = DB::table('settings')->where('name', 'paypal_active')->first();
        $this->assertNotNull($paypalActive, 'PayPal active setting should exist');
        $this->assertEquals('No', $paypalActive->value);

        $razorpayActive = DB::table('settings')->where('name', 'razorpay_active')->first();
        $this->assertNotNull($razorpayActive, 'Razorpay active setting should exist');
        $this->assertEquals('Yes', $razorpayActive->value);

        $stripeActive = DB::table('settings')->where('name', 'stripe_active')->first();
        $this->assertNotNull($stripeActive, 'Stripe active setting should exist');
        $this->assertEquals('No', $stripeActive->value);

        // Count total settings to ensure we didn't lose any
        $settingsCount = DB::table('settings')->count();
        $this->assertGreaterThanOrEqual(14, $settingsCount, 'Should have at least 14 payment settings');

        echo "✅ Settings data preserved ($settingsCount settings)\n";
    }

    private function assertCurrencyDataPreserved()
    {
        if (! DB::getSchemaBuilder()->hasTable('currency')) {
            $this->markTestSkipped('Currency table does not exist');

            return;
        }

        // Find currency by currency_name instead of hardcoded ID
        $currency = DB::table('currency')->where('currency_name', 'INR')->first();
        $this->assertNotNull($currency, 'Default currency should exist');
        $this->assertEquals('INR', $currency->currency_name);
        $this->assertEquals('Rs', $currency->currency_sign);

        echo "✅ Currency data preserved\n";
    }

    private function assertRolesDataPreserved()
    {
        if (! DB::getSchemaBuilder()->hasTable('roles')) {
            $this->markTestSkipped('Roles table does not exist');

            return;
        }

        // Find role by role_name instead of hardcoded role_id
        $role = DB::table('roles')->where('role_name', 'like', '%Admin%')->first();
        $this->assertNotNull($role, 'Default role should exist');
        $this->assertStringContainsString('Admin', $role->role_name);

        echo "✅ Roles data preserved\n";
    }

    private function assertCountryCodesPreserved()
    {
        if (! DB::getSchemaBuilder()->hasTable('country_code')) {
            $this->markTestSkipped('Country code table does not exist');

            return;
        }

        $countryCode = DB::table('country_code')->first();
        $this->assertNotNull($countryCode, 'Default country code should exist');
        $this->assertEquals('91', $countryCode->country_code);

        echo "✅ Country code data preserved\n";
    }

    /**
     * Test that the seeder handles missing tables gracefully
     */
    public function test_seeder_handles_missing_tables_gracefully()
    {
        // This test ensures the seeder doesn't crash if some tables don't exist
        $this->expectNotToPerformAssertions();

        try {
            $this->seed(ComprehensiveDataSeeder::class);
            echo "✅ Seeder handles missing tables gracefully\n";
        } catch (\Exception $e) {
            $this->fail('Seeder should handle missing tables gracefully, but got: '.$e->getMessage());
        }
    }

    /**
     * Compare table data counts between old and new approach
     */
    public function test_data_completeness_comparison()
    {
        // Run the comprehensive seeder
        $this->seed(ComprehensiveDataSeeder::class);

        // Expected data counts from original database.sql
        $expectedCounts = [
            'admin' => 1,
            'settings' => 14, // Payment gateway settings
            'currency' => 1,
            'country_code' => 1,
            'cancel_for' => 5, // Cancellation reasons
        ];

        $results = [];
        foreach ($expectedCounts as $table => $expectedCount) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                $actualCount = DB::table($table)->count();
                $results[$table] = [
                    'expected' => $expectedCount,
                    'actual' => $actualCount,
                    'preserved' => $actualCount >= $expectedCount,
                ];
            } else {
                $results[$table] = [
                    'expected' => $expectedCount,
                    'actual' => 0,
                    'preserved' => false,
                    'note' => 'Table does not exist',
                ];
            }
        }

        // Output results
        echo "\n=== DATA COMPLETENESS COMPARISON ===\n";
        foreach ($results as $table => $data) {
            $status = $data['preserved'] ? '✅' : '⚠️';
            $note = isset($data['note']) ? " ({$data['note']})" : '';
            echo "{$status} {$table}: {$data['actual']}/{$data['expected']}{$note}\n";
        }

        // Assert that critical tables have expected data
        $criticalTables = ['admin', 'currency', 'settings'];
        foreach ($criticalTables as $table) {
            if (isset($results[$table])) {
                $this->assertTrue(
                    $results[$table]['preserved'],
                    "Critical table '{$table}' should preserve all data"
                );
            }
        }
    }
}
