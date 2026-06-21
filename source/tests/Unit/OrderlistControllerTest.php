<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Controllers\Api\OrderlistController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderlistControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected OrderlistController $controller;
    protected int $storeId;
    protected int $addressId;
    protected int $userId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new OrderlistController;

        // Create required tables that don't have migrations
        $this->createRequiredTables();

        // Create test data with dynamic IDs
        $this->storeId = DB::table('store')->insertGetId([
            'store_name' => 'Test Store',
            'employee_name' => 'Test Employee',
            'phone_number' => '1234567890',
            'email' => 'test@store.com',
            'password' => 'password',
            'city' => 'Test City',
            'city_id' => 1,
            'address' => 'Test Address',
            'store_opening_time' => '09:00',
            'store_closing_time' => '21:00',
            'time_interval' => 30,
            'del_range' => 10.0,
            'lat' => '40.7128',
            'lng' => '-74.0060',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create user
        $this->userId = DB::table('users')->insertGetId([
            'name' => 'Test User',
            'email' => 'test@user.com',
            'password' => 'password',
            'reg_date' => now()->format('Y-m-d'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->addressId = DB::table('address')->insertGetId([
            'user_id' => $this->userId,
            'type' => 'home',
            'receiver_name' => 'Test User',
            'receiver_phone' => '1234567890',
            'city' => 'Test City',
            'society' => 'Test Society',
            'city_id' => 1,
            'society_id' => 1,
            'house_no' => '123',
            'landmark' => 'Test Landmark',
            'state' => 'Test State',
            'pincode' => '12345',
            'lat' => '40.7128',
            'lng' => '-74.0060',
            'select_status' => 0,
            'added_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create image storage configuration for the trait
        DB::table('image_space')->insert([
            'name' => 'test-storage',
            'url' => 'https://test.example.com',
            'aws' => 0,
            'digital_ocean' => 0,
            'local' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createRequiredTables()
    {
        // Create order_by_photo table
        DB::statement('CREATE TABLE IF NOT EXISTS order_by_photo (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            list_photo TEXT,
            address_id INT,
            store_id INT,
            processed INT DEFAULT 0,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )');

        // Create image_space table for ImageStoragePicker trait
        DB::statement('CREATE TABLE IF NOT EXISTS image_space (
            id INT AUTO_INCREMENT PRIMARY KEY,
            aws TINYINT DEFAULT 0,
            digital_ocean TINYINT DEFAULT 0,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )');
    }

    public function test_orderlist_success()
    {
        // Create a fake file upload
        Storage::fake('local');
        $file = UploadedFile::fake()->image('orderlist.jpg', 640, 480);

        $request = new Request([
            'user_id' => $this->userId,
            'address_id' => $this->addressId,
            'store_id' => $this->storeId,
        ]);
        $request->files->set('orderlist', $file);

        $response = $this->controller->orderlist($request);

        $this->assertEquals('1', $response['status']);
        $this->assertStringContainsString('Order List Submitted', $response['message']);

        // Verify database insertion
        $this->assertDatabaseHas('order_by_photo', [
            'user_id' => $this->userId,
            'address_id' => $this->addressId,
            'store_id' => $this->storeId,
            'processed' => 0,
        ]);
    }

    public function test_orderlist_duplicate_submission()
    {
        // Insert existing order
        DB::table('order_by_photo')->insert([
            'user_id' => $this->userId,
            'address_id' => $this->addressId,
            'store_id' => $this->storeId,
            'list_photo' => '/images/order/test.jpg',
            'processed' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Storage::fake('local');
        $file = UploadedFile::fake()->image('orderlist.jpg', 640, 480);

        $request = new Request([
            'user_id' => $this->userId,
            'address_id' => $this->addressId,
            'store_id' => $this->storeId,
        ]);
        $request->files->set('orderlist', $file);

        $response = $this->controller->orderlist($request);

        $this->assertEquals('2', $response['status']);
        $this->assertStringContainsString('You already submitted', $response['message']);
    }

    public function test_order_show_address_success()
    {
        // Skip for SQLite - complex distance calculations with HAVING not supported
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Distance calculations with HAVING clause not supported in SQLite');
        }

        $request = new Request([
            'user_id' => $this->userId,
            'lat' => 40.7128,  // Same as store location to ensure within range
            'lng' => -74.0060,
        ]);

        $response = $this->controller->order_show_address($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Address list', $response['message']);
        $this->assertTrue(is_array($response['data']) || $response['data'] instanceof \Illuminate\Support\Collection);
    }

    public function test_order_show_address_no_nearby_store()
    {
        $request = new Request([
            'user_id' => $this->userId,
            'lat' => 50.0,  // Far from test store
            'lng' => 50.0,
        ]);

        $response = $this->controller->order_show_address($request);

        $this->assertEquals('0', $response['status']);
        $this->assertStringContainsString('We are not delivering', $response['message']);
    }

    public function test_order_show_address_no_user_addresses()
    {
        // Skip for SQLite - complex distance calculations with HAVING not supported
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Distance calculations with HAVING clause not supported in SQLite');
        }

        // Remove test address
        DB::table('address')->where('user_id', $this->userId)->delete();

        $request = new Request([
            'user_id' => $this->userId,
            'lat' => 40.7128,  // Same as store location to ensure store is found
            'lng' => -74.0060,
        ]);

        $response = $this->controller->order_show_address($request);

        $this->assertEquals('0', $response['status']);
        $this->assertStringContainsString('Address not found', $response['message']);
        $this->assertIsArray($response['data']);
        $this->assertEmpty($response['data']);
    }

    public function test_order_show_address_out_of_delivery_range()
    {
        // Keep normal delivery range but test from a very far location
        // Store is at (40.7128, -74.0060), test from very far coordinates
        $request = new Request([
            'user_id' => $this->userId,
            'lat' => 0.0,  // Very far from NYC (basically equator)
            'lng' => 0.0,
        ]);

        $response = $this->controller->order_show_address($request);

        $this->assertEquals('0', $response['status']);
        $this->assertStringContainsString('We are not delivering', $response['message']);
    }

    public function test_orderlist_with_special_characters_in_filename()
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('order list @#$%.jpg', 640, 480);

        $request = new Request([
            'user_id' => $this->userId,
            'address_id' => $this->addressId,
            'store_id' => $this->storeId,
        ]);
        $request->files->set('orderlist', $file);

        $response = $this->controller->orderlist($request);

        $this->assertEquals('1', $response['status']);
        $this->assertStringContainsString('Order List Submitted', $response['message']);

        // Verify file path handling with special characters
        $orderRecord = DB::table('order_by_photo')->where('user_id', $this->userId)->first();
        $this->assertNotNull($orderRecord);
        $this->assertStringContainsString('order-list-@#$%.jpg', $orderRecord->list_photo);
    }
}
