<?php

declare(strict_types=1);

namespace Tests;

use App\Models\Cities;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

abstract class StoreApiTestCase extends TestCase
{
    use RefreshDatabase, WithFaker;

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

        // Set up image space configuration
        \DB::table('image_space')->insert([
            'name' => 'local',
            'url' => '/storage/images/',
            'aws' => 0,
            'digital_ocean' => 0,
            'local' => 1,
        ]);
    }

    /**
     * Create a test store with default values
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
            'orders' => 1,
        ];

        $attributes = array_merge($defaultAttributes, $attributes);

        return Store::create($attributes);
    }

    /**
     * Make authenticated API request as store
     */
    protected function storeApiCall(string $method, string $uri, array $data = []): \Illuminate\Testing\TestResponse
    {
        $data['store_id'] = $data['store_id'] ?? $this->store->id;

        return $this->json($method, '/api/store/'.ltrim($uri, '/'), $data, $this->apiHeaders);
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
     * Create test user for orders
     */
    protected function createTestUser(array $attributes = []): \App\Models\User
    {
        return \App\Models\User::factory()->create(array_merge([
            'name' => $this->faker->name,
            'user_phone' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'password' => \Hash::make('password'),
            'user_city' => $this->city->city_id,
            'reg_date' => now(),
            'wallet' => 0,
            'rewards' => 0,
            'is_verified' => 1,
            'block' => 0,
        ], $attributes));
    }

    /**
     * Create test product for store
     */
    protected function createTestProduct(array $attributes = []): \App\Models\Product
    {
        $category = \App\Models\Category::factory()->create();

        return \App\Models\Product::factory()->create(array_merge([
            'cat_id' => $category->cat_id,
            'product_name' => $this->faker->word,
            'product_image' => 'test.jpg',
            'hide' => 0,
            'added_by' => $this->store->id,
            'approved' => 1,
        ], $attributes));
    }

    /**
     * Create test order
     */
    protected function createTestOrder(array $attributes = []): \App\Models\Orders
    {
        $user = $this->createTestUser();
        $address = \App\Models\Address::factory()->create(['user_id' => $user->id]);

        return \App\Models\Orders::factory()->create(array_merge([
            'store_id' => $this->store->id,
            'user_id' => $user->id,
            'address_id' => $address->address_id,
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
            'order_status' => 'Pending',
        ], $attributes));
    }

    /**
     * Create test store order item
     */
    protected function createTestStoreOrder(string $cartId, array $attributes = []): void
    {
        \DB::table('store_orders')->insert(array_merge([
            'store_id' => $this->store->id,
            'order_cart_id' => $cartId,
            'product_name' => 'Test Product',
            'varient_id' => 1,
            'varient_image' => 'test.jpg',
            'quantity' => 1,
            'unit' => 'kg',
            'description' => 'Test product description',
            'qty' => 2,
            'price' => 50,
            'total_mrp' => 100,
            'order_date' => now(),
            'store_approval' => 1,
        ], $attributes));
    }
}
