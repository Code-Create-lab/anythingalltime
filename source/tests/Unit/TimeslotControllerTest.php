<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Controllers\Api\TimeslotController;
use Carbon\Carbon;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TimeslotControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected TimeslotController $controller;
    protected int $storeId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new TimeslotController;

        // Create test store with dynamic ID
        $this->storeId = DB::table('store')->insertGetId([
            'store_name' => 'Test Store',
            'store_opening_time' => '09:00',
            'store_closing_time' => '18:00',
            'time_interval' => 60, // 60 minute intervals
            'orders' => 5, // max orders per slot
            'employee_name' => 'Test Employee',
            'phone_number' => '1234567890',
            'email' => 'test@store.com',
            'address' => 'Test Address',
            'city' => 'Test City',
            'city_id' => 1,
            'admin_share' => 10,
            'device_id' => 'test-device',
            'password' => bcrypt('password'),
            'del_range' => 10,
            'lat' => '12.9716',
            'lng' => '77.5946',
            'admin_approval' => 1,
            'store_status' => 1,
        ]);
    }

    /**
     * Test timeslot generation for future date
     */
    public function test_timeslot_for_future_date(): void
    {
        $futureDate = Carbon::now()->addDays(1)->format('Y-m-d');

        $request = new Request([
            'store_id' => $this->storeId,
            'selected_date' => $futureDate,
        ]);

        $response = $this->controller->timeslot($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Present time Slot', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertNotEmpty($response['data']);

        // Check timeslot structure
        $firstSlot = $response['data'][0];
        $this->assertArrayHasKey('timeslot', $firstSlot);
        $this->assertArrayHasKey('availability', $firstSlot);
        $this->assertStringContainsString(' - ', $firstSlot['timeslot']);
    }

    /**
     * Test timeslot generation for today with early time
     */
    public function test_timeslot_for_today_early_time(): void
    {
        // Mock early time in the day (before 12:00)
        Carbon::setTestNow(Carbon::createFromTime(8, 0, 0));

        $todayDate = Carbon::now()->format('Y-m-d');

        $request = new Request([
            'store_id' => $this->storeId,
            'selected_date' => $todayDate,
        ]);

        $response = $this->controller->timeslot($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Present time Slot', $response['message']);
        $this->assertArrayHasKey('data', $response);

        Carbon::setTestNow(); // Reset time
    }

    /**
     * Test timeslot generation for today with late time (after 12:00 PM)
     */
    public function test_timeslot_for_today_late_time(): void
    {
        // Mock late time in the day (after 12:00)
        Carbon::setTestNow(Carbon::createFromTime(15, 0, 0));

        $todayDate = Carbon::now()->format('Y-m-d');

        $request = new Request([
            'store_id' => $this->storeId,
            'selected_date' => $todayDate,
        ]);

        $response = $this->controller->timeslot($request);

        $this->assertEquals('0', $response['status']);
        $this->assertEquals('Oops No time slot present', $response['message']);
        $this->assertArrayHasKey('data', $response);

        Carbon::setTestNow(); // Reset time
    }

    /**
     * Test timeslot for past date
     */
    public function test_timeslot_for_past_date(): void
    {
        $pastDate = Carbon::now()->subDays(1)->format('Y-m-d');

        $request = new Request([
            'store_id' => $this->storeId,
            'selected_date' => $pastDate,
        ]);

        $response = $this->controller->timeslot($request);

        $this->assertEquals('0', $response['status']);
        $this->assertEquals("You can't select the back date", $response['message']);
        $this->assertArrayHasKey('data', $response);
    }

    /**
     * Test timeslot availability calculation
     */
    public function test_timeslot_availability_calculation(): void
    {
        $futureDate = Carbon::now()->addDays(1)->format('Y-m-d');

        // Create some existing orders for specific time slots
        DB::table('orders')->insert([
            [
                'cart_id' => 'TEST001',
                'delivery_date' => $futureDate,
                'time_slot' => '09:00 am - 10:00 am',
                'user_id' => 1,
                'store_id' => $this->storeId,
                'address_id' => 1,
                'order_status' => 'Pending',
                'total_price' => 100,
                'price_without_delivery' => 90,
                'total_products_mrp' => 100,
                'order_date' => $futureDate,
                'rem_price' => 100,
            ],
            [
                'cart_id' => 'TEST002',
                'delivery_date' => $futureDate,
                'time_slot' => '09:00 am - 10:00 am',
                'user_id' => 2,
                'store_id' => $this->storeId,
                'address_id' => 1,
                'order_status' => 'Pending',
                'total_price' => 150,
                'price_without_delivery' => 140,
                'total_products_mrp' => 150,
                'order_date' => $futureDate,
                'rem_price' => 150,
            ],
        ]);

        $request = new Request([
            'store_id' => $this->storeId,
            'selected_date' => $futureDate,
        ]);

        $response = $this->controller->timeslot($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Present time Slot', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertNotEmpty($response['data']);

        // All slots should be available since we have 5 max orders and only 2 orders in first slot
        foreach ($response['data'] as $slot) {
            $this->assertEquals('available', $slot['availability']);
        }
    }

    /**
     * Test timeslot when slot is full (unavailable)
     */
    public function test_timeslot_when_slot_full(): void
    {
        $futureDate = Carbon::now()->addDays(1)->format('Y-m-d');

        // Create orders to fill the first time slot (max orders = 5)
        for ($i = 1; $i <= 5; $i++) {
            DB::table('orders')->insert([
                'cart_id' => "FULL{$i}",
                'delivery_date' => $futureDate,
                'time_slot' => '09:00 am - 10:00 am',
                'user_id' => $i,
                'store_id' => $this->storeId,
                'address_id' => 1,
                'order_status' => 'Pending',
                'total_price' => 100,
                'price_without_delivery' => 90,
                'total_products_mrp' => 100,
                'order_date' => $futureDate,
                'rem_price' => 100,
            ]);
        }

        $request = new Request([
            'store_id' => $this->storeId,
            'selected_date' => $futureDate,
        ]);

        $response = $this->controller->timeslot($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Present time Slot', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertNotEmpty($response['data']);

        // First slot should be unavailable, others available
        $firstSlot = $response['data'][0];
        $this->assertEquals('unavailable', $firstSlot['availability']);
        $this->assertEquals('09:00 am - 10:00 am', $firstSlot['timeslot']);
    }

    /**
     * Test timeslot with different store hours
     */
    public function test_timeslot_with_different_store_hours(): void
    {
        // Create store with different hours
        $storeId2 = DB::table('store')->insertGetId([
            'store_name' => 'Test Store 2',
            'store_opening_time' => '10:00',
            'store_closing_time' => '16:00',
            'time_interval' => 30, // 30 minute intervals
            'orders' => 3,
            'employee_name' => 'Test Employee 2',
            'phone_number' => '1234567891',
            'email' => 'test2@store.com',
            'address' => 'Test Address 2',
            'city' => 'Test City',
            'city_id' => 1,
            'admin_share' => 10,
            'device_id' => 'test-device-2',
            'password' => bcrypt('password'),
            'del_range' => 10,
            'lat' => '12.9716',
            'lng' => '77.5946',
            'admin_approval' => 1,
            'store_status' => 1,
        ]);

        $futureDate = Carbon::now()->addDays(1)->format('Y-m-d');

        $request = new Request([
            'store_id' => $storeId2,
            'selected_date' => $futureDate,
        ]);

        $response = $this->controller->timeslot($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Present time Slot', $response['message']);
        $this->assertArrayHasKey('data', $response);

        // Check that timeslots start at 10:00 AM and have 30-minute intervals
        $firstSlot = $response['data'][0];
        $this->assertStringStartsWith('10:00 am', $firstSlot['timeslot']);

        // Should have more slots due to 30-minute intervals
        $this->assertGreaterThan(5, count($response['data']));
    }

    /**
     * Test timeslot with non-existent store
     */
    public function test_timeslot_with_nonexistent_store(): void
    {
        $futureDate = Carbon::now()->addDays(1)->format('Y-m-d');

        $request = new Request([
            'store_id' => 999,
            'selected_date' => $futureDate,
        ]);

        // Should return proper error response for nonexistent store
        $response = $this->controller->timeslot($request);

        $this->assertEquals('0', $response['status']);
        $this->assertEquals('Store not found', $response['message']);
        $this->assertArrayHasKey('data', $response);
    }

    /**
     * Test timeslot data structure consistency
     */
    public function test_timeslot_data_structure(): void
    {
        $futureDate = Carbon::now()->addDays(1)->format('Y-m-d');

        $request = new Request([
            'store_id' => $this->storeId,
            'selected_date' => $futureDate,
        ]);

        $response = $this->controller->timeslot($request);

        $this->assertEquals('1', $response['status']);
        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('message', $response);
        $this->assertArrayHasKey('data', $response);

        foreach ($response['data'] as $slot) {
            $this->assertArrayHasKey('timeslot', $slot);
            $this->assertArrayHasKey('availability', $slot);
            $this->assertContains($slot['availability'], ['available', 'unavailable']);
            $this->assertMatchesRegularExpression('/\d{2}:\d{2} [ap]m - \d{2}:\d{2} [ap]m/', $slot['timeslot']);
        }
    }

    /**
     * Test timeslot generation edge cases
     */
    public function test_timeslot_edge_cases(): void
    {
        // Test with store that has very limited hours
        $storeId3 = DB::table('store')->insertGetId([
            'store_name' => 'Limited Hours Store',
            'store_opening_time' => '11:00',
            'store_closing_time' => '12:00',
            'time_interval' => 30,
            'orders' => 1,
            'employee_name' => 'Test Employee 3',
            'phone_number' => '1234567892',
            'email' => 'test3@store.com',
            'address' => 'Test Address 3',
            'city' => 'Test City',
            'city_id' => 1,
            'admin_share' => 10,
            'device_id' => 'test-device-3',
            'password' => bcrypt('password'),
            'del_range' => 10,
            'lat' => '12.9716',
            'lng' => '77.5946',
            'admin_approval' => 1,
            'store_status' => 1,
        ]);

        $futureDate = Carbon::now()->addDays(1)->format('Y-m-d');

        $request = new Request([
            'store_id' => $storeId3,
            'selected_date' => $futureDate,
        ]);

        $response = $this->controller->timeslot($request);

        // Should have very limited slots or possibly no slots
        if ($response['status'] === '1') {
            $this->assertLessThanOrEqual(2, count($response['data']));
        } else {
            $this->assertEquals('0', $response['status']);
        }
    }
}
