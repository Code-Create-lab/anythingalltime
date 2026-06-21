<?php

declare(strict_types=1);

namespace Tests;

use App\Models\Cities;
use App\Models\DeliveryBoy;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

abstract class DriverApiTestCase extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected DeliveryBoy $driver;

    protected Store $store;

    protected Cities $city;

    protected array $apiHeaders = [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        // Create test city
        $this->city = Cities::factory()->create([
            'city_name' => 'Test City',
        ]);

        // Create test store
        $this->store = $this->createTestStore();

        // Create test driver
        $this->driver = $this->createTestDriver();
    }

    /**
     * Create a test store
     */
    protected function createTestStore(array $attributes = []): Store
    {
        $defaultAttributes = [
            'store_name' => $this->faker->company,
            'employee_name' => $this->faker->name,
            'phone_number' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'password' => \Hash::make('password123'),
            'city' => $this->city->city_name,
            'city_id' => $this->city->city_id,
            'admin_share' => 10,
            'device_id' => $this->faker->uuid,
            'del_range' => 10,
            'lat' => $this->faker->latitude,
            'lng' => $this->faker->longitude,
            'address' => $this->faker->address,
            'admin_approval' => 1,
            'store_status' => 1,
            'store_opening_time' => '09:00',
            'store_closing_time' => '21:00',
            'time_interval' => 30,
        ];

        $attributes = array_merge($defaultAttributes, $attributes);

        return Store::create($attributes);
    }

    /**
     * Create a test delivery boy with default values
     */
    protected function createTestDriver(array $attributes = []): DeliveryBoy
    {
        $defaultAttributes = [
            'boy_name' => $this->faker->name,
            'boy_phone' => $this->faker->phoneNumber,
            'boy_city' => $this->city->city_id,
            'boy_address' => $this->faker->address,
            'password' => 'password123',
            'device_id' => $this->faker->uuid,
            'boy_loc' => $this->faker->address,
            'lat' => $this->faker->latitude,
            'lng' => $this->faker->longitude,
            'status' => 1,
            'store_id' => $this->store->id,
            'added_by' => 'store',
            'image' => 'default.jpg',
        ];

        $attributes = array_merge($defaultAttributes, $attributes);

        return DeliveryBoy::create($attributes);
    }

    /**
     * Make authenticated API request as driver
     */
    protected function driverApiCall(string $method, string $uri, array $data = []): \Illuminate\Testing\TestResponse
    {
        $data['dboy_id'] = $data['dboy_id'] ?? $this->driver->dboy_id;

        return $this->json($method, '/api/driver/'.ltrim($uri, '/'), $data, $this->apiHeaders);
    }

    /**
     * Assert standard API success response
     */
    protected function assertApiSuccess(\Illuminate\Testing\TestResponse $response, ?string $expectedMessage = null): void
    {
        $response->assertStatus(200);
        $response->assertJsonStructure(['status', 'message']);
        $response->assertJson(['status' => '1']);

        if ($expectedMessage) {
            $response->assertJson(['message' => $expectedMessage]);
        }
    }

    /**
     * Assert standard API error response
     */
    protected function assertApiError(\Illuminate\Testing\TestResponse $response, ?string $expectedMessage = null): void
    {
        $response->assertStatus(200);
        $response->assertJsonStructure(['status', 'message']);
        $response->assertJson(['status' => '0']);

        if ($expectedMessage) {
            $response->assertJson(['message' => $expectedMessage]);
        }
    }

    /**
     * Create test order assigned to driver
     */
    protected function createTestOrderForDriver(array $attributes = []): \App\Models\Orders
    {
        $user = \App\Models\User::factory()->create([
            'user_city' => $this->city->city_id,
        ]);

        $address = \App\Models\Address::factory()->create([
            'user_id' => $user->id,
            'city_id' => $this->city->city_id,
        ]);

        return \App\Models\Orders::factory()->create(array_merge([
            'store_id' => $this->store->id,
            'user_id' => $user->id,
            'address_id' => $address->address_id,
            'dboy_id' => $this->driver->dboy_id,
            'cart_id' => 'CART'.rand(10000, 99999),
            'total_price' => 100,
            'price_without_delivery' => 90,
            'delivery_charge' => 10,
            'rem_price' => 100,
            'paid_by_wallet' => 0,
            'coupon_discount' => 0,
            'delivery_date' => now()->format('Y-m-d'),
            'time_slot' => '10:00 AM - 12:00 PM',
            'payment_method' => 'COD',
            'payment_status' => 'success',
            'order_status' => 'Confirmed',
        ], $attributes));
    }

    /**
     * Create driver incentive record
     */
    protected function createDriverIncentive(array $attributes = []): void
    {
        \DB::table('driver_incentive')->insert(array_merge([
            'dboy_id' => $this->driver->dboy_id,
            'earned_till_now' => 100,
            'paid_till_now' => 50,
            'remaining' => 50,
        ], $attributes));
    }

    /**
     * Create driver bank details
     */
    protected function createDriverBankDetails(array $attributes = []): void
    {
        \DB::table('driver_bank')->insert(array_merge([
            'driver_id' => $this->driver->dboy_id,
            'ac_no' => $this->faker->bankAccountNumber,
            'ifsc' => 'TEST0001234',
            'holder_name' => $this->driver->boy_name,
            'bank_name' => $this->faker->company.' Bank',
            'upi' => $this->faker->email,
        ], $attributes));
    }
}
