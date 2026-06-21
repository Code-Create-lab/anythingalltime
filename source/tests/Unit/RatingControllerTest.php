<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Controllers\Api\RatingController;
use App\Models\User;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RatingControllerTest extends TestCase
{
    use WithFaker;

    protected RatingController $controller;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new RatingController;

        // Create test user
        $this->user = User::factory()->create([
            'name' => 'Test User',
        ]);

        // Create test tables for SQLite
        DB::statement('CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY,
            cart_id TEXT,
            user_id INTEGER,
            order_status TEXT,
            dboy_id INTEGER,
            store_id INTEGER,
            total_price REAL DEFAULT 0,
            address_id INTEGER DEFAULT 1
        )');

        DB::statement('CREATE TABLE IF NOT EXISTS delivery_rating (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            cart_id TEXT,
            user_id INTEGER,
            rating INTEGER,
            dboy_id INTEGER,
            description TEXT
        )');

        DB::statement('CREATE TABLE IF NOT EXISTS product_rating (
            id INTEGER PRIMARY KEY,
            user_id INTEGER,
            varient_id INTEGER,
            store_id INTEGER,
            rating INTEGER,
            description TEXT,
            created_at TEXT,
            updated_at TEXT
        )');

        DB::statement('CREATE TABLE IF NOT EXISTS store_orders (
            id INTEGER PRIMARY KEY,
            order_cart_id TEXT,
            varient_id INTEGER,
            store_id INTEGER,
            product_name TEXT,
            varient_image TEXT,
            quantity INTEGER,
            unit TEXT,
            qty INTEGER,
            price REAL,
            total_mrp REAL,
            description TEXT,
            order_date TEXT,
            store_approval INTEGER
        )');

        DB::statement('CREATE TABLE IF NOT EXISTS product_varient (
            varient_id INTEGER PRIMARY KEY,
            product_id INTEGER,
            quantity INTEGER,
            unit TEXT,
            base_price REAL,
            description TEXT,
            varient_image TEXT
        )');
    }

    /**
     * Test delivery review with completed order
     */
    public function test_review_on_delivery_successful(): void
    {
        // Create completed order
        DB::table('orders')->insert([
            'cart_id' => 'COMPLETED123',
            'user_id' => $this->user->id,
            'order_status' => 'Completed',
            'dboy_id' => 1,
            'store_id' => 1,
            'total_price' => 100,
            'price_without_delivery' => 90,
            'total_products_mrp' => 120,
            'address_id' => 1,
            'order_date' => now()->format('Y-m-d'),
            'delivery_date' => now()->format('Y-m-d'),
            'time_slot' => '10:00 AM - 12:00 PM',
        ]);

        $request = new Request([
            'user_id' => $this->user->id,
            'cart_id' => 'COMPLETED123',
            'rating' => 5,
            'description' => 'Excellent delivery service',
        ]);

        $response = $this->controller->review_on_delivery($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('reviewed successfully', $response['message']);

        // Verify delivery rating was inserted
        $this->assertDatabaseHas('delivery_rating', [
            'cart_id' => 'COMPLETED123',
            'user_id' => $this->user->id,
            'rating' => 5,
            'dboy_id' => 1,
            'description' => 'Excellent delivery service',
        ]);
    }

    /**
     * Test delivery review with non-completed order
     */
    public function test_review_on_delivery_order_not_completed(): void
    {
        // Create pending order
        DB::table('orders')->insert([
            'cart_id' => 'PENDING123',
            'user_id' => $this->user->id,
            'order_status' => 'Pending',
            'dboy_id' => 1,
            'store_id' => 1,
            'total_price' => 100,
            'price_without_delivery' => 90,
            'total_products_mrp' => 120,
            'address_id' => 1,
            'order_date' => now()->format('Y-m-d'),
            'delivery_date' => now()->format('Y-m-d'),
            'time_slot' => '10:00 AM - 12:00 PM',
        ]);

        $request = new Request([
            'user_id' => $this->user->id,
            'cart_id' => 'PENDING123',
            'rating' => 5,
            'description' => 'Good service',
        ]);

        $response = $this->controller->review_on_delivery($request);

        $this->assertEquals('0', $response['status']);
        $this->assertEquals('Please Wait for Order Completion', $response['message']);
    }

    /**
     * Test delivery review with non-existent order
     */
    public function test_review_on_delivery_order_not_found(): void
    {
        $request = new Request([
            'user_id' => $this->user->id,
            'cart_id' => 'NONEXISTENT',
            'rating' => 5,
            'description' => 'Good service',
        ]);

        $response = $this->controller->review_on_delivery($request);

        $this->assertEquals('0', $response['status']);
        $this->assertEquals('Please Wait for Order Completion', $response['message']);
    }

    /**
     * Test delivery review description handling (undefined variable bug)
     */
    public function test_review_on_delivery_description_handling(): void
    {
        // Create completed order
        DB::table('orders')->insert([
            'cart_id' => 'DESC123',
            'user_id' => $this->user->id,
            'order_status' => 'Completed',
            'dboy_id' => 1,
            'store_id' => 1,
            'total_price' => 100,
            'price_without_delivery' => 90,
            'total_products_mrp' => 120,
            'address_id' => 1,
            'order_date' => now()->format('Y-m-d'),
            'delivery_date' => now()->format('Y-m-d'),
            'time_slot' => '10:00 AM - 12:00 PM',
        ]);

        $request = new Request([
            'user_id' => $this->user->id,
            'cart_id' => 'DESC123',
            'rating' => 4,
            'description' => null, // Test null description handling
        ]);

        $response = $this->controller->review_on_delivery($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('reviewed successfully', $response['message']);

        // Note: Due to bug in controller, description becomes 'N/A'
        $this->assertDatabaseHas('delivery_rating', [
            'cart_id' => 'DESC123',
            'user_id' => $this->user->id,
            'rating' => 4,
            'description' => 'N/A',
        ]);
    }

    /**
     * Test adding new product rating
     */
    public function test_add_product_rating_new(): void
    {
        $request = new Request([
            'user_id' => $this->user->id,
            'store_id' => 1,
            'varient_id' => 1,
            'rating' => 5,
            'description' => 'Great product!',
        ]);

        $response = $this->controller->add_product_rating($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('reviewed successfully', $response['message']);

        // Verify product rating was inserted
        $this->assertDatabaseHas('product_rating', [
            'user_id' => $this->user->id,
            'store_id' => 1,
            'varient_id' => 1,
            'rating' => 5,
            'description' => 'Great product!',
        ]);
    }

    /**
     * Test updating existing product rating
     */
    public function test_add_product_rating_update_existing(): void
    {
        // Create existing rating
        DB::table('product_rating')->insert([
            'user_id' => $this->user->id,
            'store_id' => 1,
            'varient_id' => 1,
            'rating' => 3,
            'description' => 'OK product',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = new Request([
            'user_id' => $this->user->id,
            'store_id' => 1,
            'varient_id' => 1,
            'rating' => 5,
            'description' => 'Actually great product!',
        ]);

        $response = $this->controller->add_product_rating($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('reviewed successfully', $response['message']);

        // Verify product rating was updated
        $this->assertDatabaseHas('product_rating', [
            'user_id' => $this->user->id,
            'store_id' => 1,
            'varient_id' => 1,
            'rating' => 5,
            'description' => 'Actually great product!',
        ]);

        // Verify old rating is gone
        $this->assertDatabaseMissing('product_rating', [
            'user_id' => $this->user->id,
            'store_id' => 1,
            'varient_id' => 1,
            'rating' => 3,
            'description' => 'OK product',
        ]);
    }

    /**
     * Test check for rating - user can review
     */
    public function test_check_for_rating_can_review(): void
    {
        // Create completed order with store_orders
        DB::table('orders')->insert([
            'cart_id' => 'CANREVIEW123',
            'user_id' => $this->user->id,
            'order_status' => 'Completed',
            'store_id' => 1,
            'total_price' => 100,
            'price_without_delivery' => 90,
            'total_products_mrp' => 120,
            'address_id' => 1,
            'order_date' => now()->format('Y-m-d'),
            'delivery_date' => now()->format('Y-m-d'),
            'time_slot' => '10:00 AM - 12:00 PM',
        ]);

        DB::table('store_orders')->insert([
            'order_cart_id' => 'CANREVIEW123',
            'varient_id' => 1,
            'store_id' => 1,
            'product_name' => 'Test Product',
            'varient_image' => 'test.jpg',
            'quantity' => 1,
            'unit' => 'kg',
            'qty' => 1,
            'price' => 100,
            'total_mrp' => 120,
            'description' => 'Test description',
            'order_date' => now(),
            'store_approval' => 1,
        ]);

        DB::table('product_varient')->insert([
            'varient_id' => 1,
            'product_id' => 1,
            'quantity' => 500,
            'unit' => 'g',
            'base_price' => 100,
            'description' => 'Test variant',
            'varient_image' => 'test.jpg',
        ]);

        $request = new Request([
            'user_id' => $this->user->id,
            'varient_id' => 1,
            'store_id' => 1,
        ]);

        $response = $this->controller->check_for_rating($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('User Can Review', $response['message']);
    }

    /**
     * Test check for rating - user already reviewed
     */
    public function test_check_for_rating_already_reviewed(): void
    {
        // Create completed order with store_orders
        DB::table('orders')->insert([
            'cart_id' => 'REVIEWED123',
            'user_id' => $this->user->id,
            'order_status' => 'Completed',
            'store_id' => 1,
            'total_price' => 100,
            'price_without_delivery' => 90,
            'total_products_mrp' => 120,
            'address_id' => 1,
            'order_date' => now()->format('Y-m-d'),
            'delivery_date' => now()->format('Y-m-d'),
            'time_slot' => '10:00 AM - 12:00 PM',
        ]);

        DB::table('store_orders')->insert([
            'order_cart_id' => 'REVIEWED123',
            'varient_id' => 2,
            'store_id' => 1,
            'product_name' => 'Test Product',
            'varient_image' => 'test.jpg',
            'quantity' => 1,
            'unit' => 'kg',
            'qty' => 1,
            'price' => 100,
            'total_mrp' => 120,
            'description' => 'Test description',
            'order_date' => now(),
            'store_approval' => 1,
        ]);

        DB::table('product_varient')->insert([
            'varient_id' => 2,
            'product_id' => 1,
            'quantity' => 500,
            'unit' => 'g',
            'base_price' => 100,
            'description' => 'Test variant',
            'varient_image' => 'test.jpg',
        ]);

        // Create existing rating
        DB::table('product_rating')->insert([
            'user_id' => $this->user->id,
            'varient_id' => 2,
            'store_id' => 1,
            'rating' => 4,
            'description' => 'Good product',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = new Request([
            'user_id' => $this->user->id,
            'varient_id' => 2,
            'store_id' => 1,
        ]);

        $response = $this->controller->check_for_rating($request);

        $this->assertEquals('2', $response['status']);
        $this->assertEquals('User Already Reviewed', $response['message']);
    }

    /**
     * Test check for rating - user cannot review
     */
    public function test_check_for_rating_cannot_review(): void
    {
        $request = new Request([
            'user_id' => $this->user->id,
            'varient_id' => 999,
            'store_id' => 1,
        ]);

        $response = $this->controller->check_for_rating($request);

        $this->assertEquals('0', $response['status']);
        $this->assertEquals('User Cannot Reviewed', $response['message']);
    }

    /**
     * Test get product rating with data
     */
    public function test_get_product_rating_with_data(): void
    {
        // Create product ratings
        DB::table('product_rating')->insert([
            'user_id' => $this->user->id,
            'varient_id' => 3,
            'store_id' => 1,
            'rating' => 5,
            'description' => 'Excellent product',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = new Request([
            'varient_id' => 3,
            'store_id' => 1,
        ]);

        $response = $this->controller->get_product_rating($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Product Rating', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertNotEmpty($response['data']);
    }

    /**
     * Test get product rating without data
     */
    public function test_get_product_rating_without_data(): void
    {
        $request = new Request([
            'varient_id' => 999,
            'store_id' => 999,
        ]);

        $response = $this->controller->get_product_rating($request);

        // Note: Controller always returns status 1 even when no data
        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Product Rating', $response['message']);
        $this->assertArrayHasKey('data', $response);
    }

    /**
     * Test response structure consistency
     */
    public function test_response_structure_consistency(): void
    {
        $methods = [
            ['review_on_delivery', ['user_id' => $this->user->id, 'cart_id' => 'TEST', 'rating' => 5]],
            ['add_product_rating', ['user_id' => $this->user->id, 'store_id' => 1, 'varient_id' => 1, 'rating' => 5, 'description' => 'Test description']],
            ['check_for_rating', ['user_id' => $this->user->id, 'varient_id' => 1, 'store_id' => 1]],
        ];

        foreach ($methods as [$method, $params]) {
            $request = new Request($params);
            $response = $this->controller->$method($request);

            $this->assertArrayHasKey('status', $response);
            $this->assertArrayHasKey('message', $response);
        }
    }
}
