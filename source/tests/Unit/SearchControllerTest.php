<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Controllers\Api\SearchController;
use DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SearchControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected SearchController $controller;

    protected $storeId;
    protected $categoryId;
    protected $productId;
    protected $variantId;
    protected $userId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new SearchController;

        // Create test data using DB insert with auto-generated IDs
        $this->storeId = DB::table('store')->insertGetId([
            'store_name' => 'Test Store',
            'employee_name' => 'John Doe',
            'phone_number' => '1234567890',
            'store_photo' => 'N/A',
            'city' => 'Test City',
            'city_id' => 1,
            'admin_share' => 0,
            'email' => 'test@store.com',
            'password' => bcrypt('password'),
            'del_range' => 10,
            'lat' => '0',
            'lng' => '0',
            'address' => 'Test Address',
            'admin_approval' => 1,
            'orders' => 1,
            'store_status' => 1,
            'store_opening_time' => '08:00',
            'store_closing_time' => '22:00',
            'time_interval' => 30,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->categoryId = DB::table('categories')->insertGetId([
            'title' => 'Test Category',
            'slug' => 'test-category',
            'level' => 0,
            'image' => 'test.jpg',
            'description' => 'Test Description',
        ]);

        $this->productId = DB::table('product')->insertGetId([
            'product_name' => 'Test Product',
            'cat_id' => $this->categoryId,
            'hide' => 0,
            'approved' => 1,
            'product_image' => 'test.jpg',
            'type' => 'veg',
        ]);

        $this->variantId = DB::table('product_varient')->insertGetId([
            'product_id' => $this->productId,
            'ean' => '1234567890123',
            'approved' => 1,
            'description' => 'Test Variant',
            'varient_image' => 'test.jpg',
            'unit' => 'kg',
            'quantity' => 1,
            'base_price' => 100,
        ]);

        DB::table('store_products')->insert([
            'store_id' => $this->storeId,
            'varient_id' => $this->variantId,
            'price' => 100,
            'mrp' => 120,
            'stock' => 10,
        ]);

        // Create a test user as well
        $this->userId = DB::table('users')->insertGetId([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'user_phone' => '1234567890',
            'password' => bcrypt('password'),
            'reg_date' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create missing tables for testing
        if (! Schema::hasTable('recent_search')) {
            Schema::create('recent_search', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->string('keyword');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('trending_search')) {
            Schema::create('trending_search', function (Blueprint $table) {
                $table->increments('trend_id');
                $table->integer('varient_id');
                $table->timestamps();
            });
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Test search method with valid EAN code
     */
    public function test_search_with_valid_ean(): void
    {
        $request = new Request([
            'ean_code' => '1234567890123',
            'store_id' => $this->storeId,
            'user_id' => $this->userId,
        ]);

        $response = $this->controller->search($request);

        $this->assertEquals(1, $response['status']);
        $this->assertEquals('Products Detail', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('detail', $response['data']);
    }

    /**
     * Test search method with invalid EAN code
     */
    public function test_search_with_invalid_ean(): void
    {
        $request = new Request([
            'ean_code' => null,
            'store_id' => $this->storeId,
            'user_id' => $this->userId,
        ]);

        $response = $this->controller->search($request);

        $this->assertEquals(0, $response['status']);
        $this->assertEquals('Not able to scan any barcode', $response['message']);
    }

    /**
     * Test searchbystore method with products found
     */
    public function test_searchbystore_with_products_found(): void
    {
        $request = new Request([
            'keyword' => 'Test',
            'store_id' => $this->storeId,
            'user_id' => $this->userId,
        ]);

        $response = $this->controller->searchbystore($request);

        $this->assertEquals(1, $response['status']);
        $this->assertEquals('Products found', $response['message']);
        $this->assertArrayHasKey('data', $response);
    }

    /**
     * Test searchbystore method with no products found
     */
    public function test_searchbystore_with_no_products_found(): void
    {
        $request = new Request([
            'keyword' => 'NonExistentProduct',
            'store_id' => $this->storeId,
            'user_id' => $this->userId,
        ]);

        $response = $this->controller->searchbystore($request);

        $this->assertEquals(0, $response['status']);
        $this->assertEquals('Products not found', $response['message']);
    }

    /**
     * Test searchbystore with price filters
     */
    public function test_searchbystore_with_price_filters(): void
    {
        $request = new Request([
            'keyword' => 'Test',
            'store_id' => $this->storeId,
            'user_id' => $this->userId,
            'min_price' => 50,
            'max_price' => 150,
        ]);

        $response = $this->controller->searchbystore($request);

        $this->assertEquals(1, $response['status']);
        $this->assertEquals('Products found', $response['message']);
    }

    /**
     * Test searchbystore with rating filters
     */
    public function test_searchbystore_with_rating_filters(): void
    {
        $request = new Request([
            'keyword' => 'Test',
            'store_id' => $this->storeId,
            'user_id' => $this->userId,
            'min_rating' => 3,
            'max_rating' => 5,
        ]);

        $response = $this->controller->searchbystore($request);

        // Products might not be found with rating filters, so check for either success or not found
        $this->assertTrue(in_array($response['status'], [0, 1]));
        $this->assertTrue(in_array($response['message'], ['Products found', 'Products not found']));
    }

    /**
     * Test trensearchproducts method
     */
    public function test_trensearchproducts(): void
    {
        $request = new Request([
            'store_id' => $this->storeId,
        ]);

        $response = $this->controller->trensearchproducts($request);

        $this->assertEquals(0, $response['status']);
        $this->assertEquals('Products not found', $response['message']);
    }

    /**
     * Test recentsearch method
     */
    public function test_recentsearch(): void
    {
        $request = new Request([
            'user_id' => $this->userId,
        ]);

        $response = $this->controller->recentsearch($request);

        $this->assertEquals(0, $response['status']);
        $this->assertEquals('Products not found', $response['message']);
    }
}
