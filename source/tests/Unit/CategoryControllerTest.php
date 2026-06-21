<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Controllers\Api\CategoryController;
use App\Models\Category;
use App\Models\Cities;
use App\Models\DealProduct;
use App\Models\Orders;
use App\Models\Product;
use App\Models\ProductRating;
use App\Models\ProductVariant;
use App\Models\StoreBanner;
use App\Models\StoreOrders;
use App\Models\StoreProducts;
use App\Models\Stores;
use App\Models\User;
use App\Models\Wishlist;
use Carbon\Carbon;
use DB;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use WithFaker;

    protected CategoryController $controller;

    protected User $user;

    protected Stores $store;

    protected ProductVariant $productVariant;

    protected Product $product;

    protected StoreProducts $storeProduct;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new CategoryController;

        // Create test user
        $this->user = User::factory()->create([
            'user_phone' => '1234567890',
        ]);

        // Create test store
        $this->store = Stores::factory()->create([
            'store_name' => 'Test Store',
            'lat' => '12.9716',
            'lng' => '77.5946',
            'del_range' => 10,
            'employee_name' => 'Test Employee',
            'phone_number' => '1234567890',
            'email' => 'test@store.com',
            'address' => 'Test Address',
            'city' => 'Test City',
            'city_id' => 1,
            'admin_share' => 10.0,
            'device_id' => 'test-device',
            'password' => bcrypt('password'),
            'admin_approval' => 1,
            'orders' => 1,
            'store_status' => 1,
            'store_opening_time' => '09:00',
            'store_closing_time' => '18:00',
            'time_interval' => 30,
        ]);

        // Create test city
        Cities::factory()->create([
            'city_id' => 1,
            'city_name' => 'Test City',
        ]);

        // Create parent category
        $parentCategory = Category::factory()->create([
            'title' => 'Parent Category',
            'parent' => 0,
            'level' => 0,
        ]);

        // Create child category
        $this->category = Category::factory()->create([
            'title' => 'Test Category',
            'parent' => $parentCategory->cat_id,
            'level' => 1,
            'image' => 'category.jpg',
            'description' => 'Test category description',
        ]);

        // Create test product
        $this->product = Product::factory()->create([
            'cat_id' => $this->category->cat_id,
            'product_name' => 'Test Product',
            'product_image' => 'test.jpg',
            'type' => 'Regular',
            'hide' => 0,
            'approved' => 1,
        ]);

        // Create product variant
        $this->productVariant = ProductVariant::factory()->create([
            'product_id' => $this->product->product_id,
            'description' => 'Test variant',
            'varient_image' => 'variant.jpg',
            'quantity' => 1,
            'unit' => 'kg',
            'approved' => 1,
        ]);

        // Create store product
        $this->storeProduct = StoreProducts::factory()->create([
            'store_id' => $this->store->id,
            'varient_id' => $this->productVariant->varient_id,
            'price' => 100,
            'mrp' => 120,
            'stock' => 50,
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test product_images method with product images available
     */
    public function test_product_images_with_images_available(): void
    {
        DB::table('product_images')->insert([
            'product_id' => $this->product->product_id,
            'image' => 'image1.jpg',
            'type' => 1,
        ]);

        DB::table('product_images')->insert([
            'product_id' => $this->product->product_id,
            'image' => 'image2.jpg',
            'type' => 0,
        ]);

        $request = new Request(['product_id' => $this->product->product_id]);
        $response = $this->controller->product_images($request);

        $this->assertEquals(1, $response['status']);
        $this->assertEquals('Product Images', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertCount(2, $response['data']);
    }

    /**
     * Test product_images method with no product images but product exists
     */
    public function test_product_images_fallback_to_product(): void
    {
        $request = new Request(['product_id' => $this->product->product_id]);
        $response = $this->controller->product_images($request);

        $this->assertEquals(1, $response['status']);
        $this->assertEquals('Product Images', $response['message']);
        $this->assertArrayHasKey('data', $response);
    }

    /**
     * Test product_images method with non-existent product
     */
    public function test_product_images_with_non_existent_product(): void
    {
        $request = new Request(['product_id' => 999]);
        $response = $this->controller->product_images($request);

        $this->assertEquals(0, $response['status']);
        $this->assertEquals('no images found', $response['message']);
    }

    /**
     * Test getneareststore method with store in range
     */
    public function test_getneareststore_with_store_in_range(): void
    {
        $request = new Request([
            'lat' => '12.9716',
            'lng' => '77.5946',
        ]);

        $response = $this->controller->getneareststore($request);

        $this->assertEquals(1, $response['status']);
        $this->assertEquals('Store Found at your location', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('Test Store', $response['data']->store_name);
    }

    /**
     * Test getneareststore method with no stores available
     */
    public function test_getneareststore_with_no_stores(): void
    {
        // Delete the store to simulate no stores available
        $this->store->delete();

        $request = new Request([
            'lat' => '12.9716',
            'lng' => '77.5946',
        ]);

        $response = $this->controller->getneareststore($request);

        $this->assertEquals(0, $response['status']);
        $this->assertStringContainsString('We are not delivering your area', $response['message']);
    }

    /**
     * Test oneapi method (homepage data)
     */
    public function test_oneapi_homepage_data(): void
    {
        // Create banner
        StoreBanner::factory()->create([
            'store_id' => $this->store->id,
            'cat_id' => $this->category->cat_id,
            'image' => 'banner.jpg',
        ]);

        // Create second banner
        DB::table('sec_banner')->insert([
            'store_id' => $this->store->id,
            'image' => 'sec_banner.jpg',
            'banner_name' => 'Test Banner',
            'varient_id' => $this->productVariant->varient_id,
            'product_name' => 'Test Product',
        ]);

        $request = new Request([
            'store_id' => $this->store->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->controller->oneapi($request);

        $this->assertEquals(1, $response['status']);
        $this->assertEquals('Homepage data', $response['message']);
        $this->assertArrayHasKey('banner', $response);
        $this->assertArrayHasKey('second_banner', $response);
        $this->assertArrayHasKey('top_cat', $response);
        $this->assertArrayHasKey('category', $response);
    }

    /**
     * Test cate method for category listing
     */
    public function test_cate_category_listing(): void
    {
        $request = new Request([
            'store_id' => $this->store->id,
            'byname' => 'asc',
            'latest' => 'asc',
        ]);

        $response = $this->controller->cate($request);

        $this->assertEquals(1, $response['status']);
        $this->assertEquals('data found', $response['message']);
        $this->assertArrayHasKey('data', $response);
    }

    /**
     * Test top_cat method
     */
    public function test_top_cat_method(): void
    {
        // Create some orders to make categories "top"
        StoreOrders::factory()->create([
            'store_id' => $this->store->id,
            'varient_id' => $this->productVariant->varient_id,
            'order_cart_id' => 'ORDER123',
        ]);

        Orders::factory()->create([
            'cart_id' => 'ORDER123',
            'store_id' => $this->store->id,
            'user_id' => $this->user->id,
        ]);

        $request = new Request(['store_id' => $this->store->id]);
        $response = $this->controller->top_cat($request);

        $this->assertEquals(1, $response['status']);
        $this->assertEquals('Top Categories', $response['message']);
        $this->assertArrayHasKey('data', $response);
    }

    /**
     * Test tag_product method with existing tags
     */
    public function test_tag_product_with_existing_tags(): void
    {
        DB::table('tags')->insert([
            'product_id' => $this->product->product_id,
            'tag' => 'Test',
        ]);

        $request = new Request([
            'tag_name' => 'test',
            'store_id' => $this->store->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->controller->tag_product($request);

        $this->assertEquals(1, $response['status']);
        $this->assertEquals('Products found', $response['message']);
        $this->assertArrayHasKey('data', $response);
    }

    /**
     * Test tag_product method with non-existent tags
     */
    public function test_tag_product_with_non_existent_tags(): void
    {
        $request = new Request([
            'tag_name' => 'nonexistent',
            'store_id' => $this->store->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->controller->tag_product($request);

        $this->assertEquals(0, $response['status']);
        $this->assertEquals('Products not found', $response['message']);
    }

    /**
     * Test banner_var method with valid variant
     */
    public function test_banner_var_with_valid_variant(): void
    {
        $request = new Request([
            'varient_id' => $this->productVariant->varient_id,
            'store_id' => $this->store->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->controller->banner_var($request);

        $this->assertEquals(1, $response['status']);
        $this->assertEquals('Products Detail', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('detail', $response['data']);
    }

    /**
     * Test banner_var method with invalid variant
     */
    public function test_banner_var_with_invalid_variant(): void
    {
        $request = new Request([
            'varient_id' => 999,
            'store_id' => $this->store->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->controller->banner_var($request);

        $this->assertEquals(0, $response['status']);
        $this->assertEquals('Product not found', $response['message']);
    }

    /**
     * Test product_det method with valid product
     */
    public function test_product_det_with_valid_product(): void
    {
        $request = new Request([
            'product_id' => $this->product->product_id,
            'store_id' => $this->store->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->controller->product_det($request);

        $this->assertEquals(1, $response['status']);
        $this->assertEquals('Products Detail', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('detail', $response['data']);
        $this->assertArrayHasKey('similar_product', $response['data']);
    }

    /**
     * Test product_det method with invalid product
     */
    public function test_product_det_with_invalid_product(): void
    {
        $request = new Request([
            'product_id' => 999,
            'store_id' => $this->store->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->controller->product_det($request);

        $this->assertEquals(0, $response['status']);
        $this->assertEquals('Product not found', $response['message']);
    }

    /**
     * Test wishlist functionality integration
     */
    public function test_product_with_wishlist_status(): void
    {
        // Add product to wishlist
        Wishlist::factory()->create([
            'user_id' => $this->user->id,
            'varient_id' => $this->productVariant->varient_id,
            'store_id' => $this->store->id,
        ]);

        $request = new Request([
            'product_id' => $this->product->product_id,
            'store_id' => $this->store->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->controller->product_det($request);

        $this->assertEquals(1, $response['status']);
        $this->assertEquals('true', $response['data']['detail']->isFavourite);
    }

    /**
     * Test product rating functionality
     */
    public function test_product_with_rating(): void
    {
        ProductRating::factory()->create([
            'varient_id' => $this->productVariant->varient_id,
            'store_id' => $this->store->id,
            'user_id' => $this->user->id,
            'rating' => 4,
        ]);

        ProductRating::factory()->create([
            'varient_id' => $this->productVariant->varient_id,
            'store_id' => $this->store->id,
            'user_id' => 2,
            'rating' => 5,
        ]);

        $request = new Request([
            'product_id' => $this->product->product_id,
            'store_id' => $this->store->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->controller->product_det($request);

        $this->assertEquals(1, $response['status']);
        $this->assertEquals(4.5, $response['data']['detail']->avgrating);
        $this->assertEquals(2, $response['data']['detail']->countrating);
    }

    /**
     * Test discount calculation
     */
    public function test_discount_calculation(): void
    {
        $request = new Request([
            'product_id' => $this->product->product_id,
            'store_id' => $this->store->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->controller->product_det($request);

        $this->assertEquals(1, $response['status']);
        // MRP: 120, Price: 100, Discount: 16.67%
        $this->assertEquals(16.67, $response['data']['detail']->discountper);
    }

    /**
     * Test deal product price override
     */
    public function test_deal_product_price_override(): void
    {
        DealProduct::factory()->create([
            'varient_id' => $this->productVariant->varient_id,
            'store_id' => $this->store->id,
            'deal_price' => 80,
            'valid_from' => Carbon::now()->subDay(),
            'valid_to' => Carbon::now()->addDay(),
        ]);

        $request = new Request([
            'product_id' => $this->product->product_id,
            'store_id' => $this->store->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->controller->product_det($request);

        $this->assertEquals(1, $response['status']);
        $this->assertEquals(80, $response['data']['detail']->price);
    }

    /**
     * Test expired deal product doesn't affect price
     */
    public function test_expired_deal_product_no_price_override(): void
    {
        DealProduct::factory()->create([
            'varient_id' => $this->productVariant->varient_id,
            'store_id' => $this->store->id,
            'deal_price' => 80,
            'valid_from' => Carbon::now()->subDays(5),
            'valid_to' => Carbon::now()->subDay(), // Expired
        ]);

        $request = new Request([
            'product_id' => $this->product->product_id,
            'store_id' => $this->store->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->controller->product_det($request);

        $this->assertEquals(1, $response['status']);
        $this->assertEquals(100, $response['data']['detail']->price); // Original price
    }

    /**
     * Test cart quantity integration
     */
    public function test_product_with_cart_quantity(): void
    {
        // Add product to cart
        StoreOrders::factory()->create([
            'store_id' => $this->store->id,
            'store_approval' => $this->user->id,
            'order_cart_id' => 'incart',
            'varient_id' => $this->productVariant->varient_id,
            'qty' => 3,
        ]);

        $request = new Request([
            'product_id' => $this->product->product_id,
            'store_id' => $this->store->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->controller->product_det($request);

        $this->assertEquals(1, $response['status']);
        $this->assertEquals(3, $response['data']['detail']->cart_qty);
    }

    /**
     * Test anonymous user functionality
     */
    public function test_product_det_without_user_id(): void
    {
        $request = new Request([
            'product_id' => $this->product->product_id,
            'store_id' => $this->store->id,
        ]);

        $response = $this->controller->product_det($request);

        $this->assertEquals(1, $response['status']);
        $this->assertEquals('false', $response['data']['detail']->isFavourite);
        $this->assertEquals(0, $response['data']['detail']->cart_qty);
    }
}
