<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Controllers\Api\CartController;
use App\Models\Address;
use App\Models\Category;
use App\Models\DealProduct;
use App\Models\MinimumMaximumOrderValue;
use App\Models\Orders;
use App\Models\Product;
use App\Models\ProductRating;
use App\Models\ProductVariant;
use App\Models\ServiceArea;
use App\Models\StoreOrders;
use App\Models\StoreProducts;
use App\Models\Stores;
use App\Models\User;
use App\Models\Wishlist;
use Carbon\Carbon;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use WithFaker;

    protected CartController $controller;

    protected User $user;

    protected Stores $store;

    protected ProductVariant $productVariant;

    protected Product $product;

    protected StoreProducts $storeProduct;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new CartController;

        // Create test data
        $this->user = User::factory()->create([
            'user_phone' => '1234567890',
            'wallet' => 1000,
        ]);

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

        // Create parent category first
        $parentCategory = Category::factory()->create([
            'title' => 'Parent Category',
            'parent' => 0,
            'level' => 1,
        ]);

        // Create child category
        $this->category = Category::factory()->create([
            'title' => 'Test Category',
            'parent' => $parentCategory->cat_id,
            'level' => 2,
            'tax_per' => 10,
            'tax_type' => 1,
            'tax_name' => 'GST',
        ]);

        $this->product = Product::factory()->create([
            'cat_id' => $this->category->cat_id,
            'product_name' => 'Test Product',
            'product_image' => 'test.jpg',
            'type' => 'Regular',
        ]);

        $this->productVariant = ProductVariant::factory()->create([
            'product_id' => $this->product->product_id,
            'description' => 'Test variant',
            'varient_image' => 'variant.jpg',
            'quantity' => 1,
            'unit' => 'kg',
        ]);

        $this->storeProduct = StoreProducts::factory()->create([
            'store_id' => $this->store->id,
            'varient_id' => $this->productVariant->varient_id,
            'price' => 100,
            'mrp' => 120,
            'stock' => 50,
            'min_ord_qty' => 1,
            'max_ord_qty' => 100,
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test adding item to cart when cart is empty
     */
    public function test_add_to_cart_when_cart_is_empty(): void
    {
        $request = new Request([
            'user_id' => $this->user->id,
            'store_id' => $this->store->id,
            'varient_id' => $this->productVariant->varient_id,
            'qty' => 2,
        ]);

        $response = $this->controller->add_to_cart($request);

        $this->assertEquals(1, $response['status']);
        $this->assertEquals('Cart Updated', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('total_price', $response['data']);
        $this->assertArrayHasKey('total_items', $response['data']);
        $this->assertEquals(1, $response['data']['total_items']);
    }

    /**
     * Test adding item to cart when cart has items from different store
     */
    public function test_add_to_cart_when_cart_has_items_from_different_store(): void
    {
        // Create a different store
        $differentStore = Stores::factory()->create([
            'store_name' => 'Different Store',
        ]);

        // Add item to cart from different store
        StoreOrders::factory()->create([
            'store_id' => $differentStore->id,
            'store_approval' => $this->user->id,
            'order_cart_id' => 'incart',
        ]);

        $request = new Request([
            'user_id' => $this->user->id,
            'store_id' => $this->store->id,
            'varient_id' => $this->productVariant->varient_id,
            'qty' => 2,
        ]);

        $response = $this->controller->add_to_cart($request);

        $this->assertEquals(2, $response['status']);
        $this->assertStringContainsString('previous cart items will be cleared', $response['message']);
    }

    /**
     * Test adding item to cart when quantity exceeds stock
     */
    public function test_add_to_cart_when_quantity_exceeds_stock(): void
    {
        $request = new Request([
            'user_id' => $this->user->id,
            'store_id' => $this->store->id,
            'varient_id' => $this->productVariant->varient_id,
            'qty' => 60, // More than available stock (50) but less than max_ord_qty (100)
        ]);

        $response = $this->controller->add_to_cart($request);

        $this->assertEquals(0, $response['status']);
        $this->assertStringContainsString('only 50 available in stock', $response['message']);
    }

    /**
     * Test adding item to cart when quantity exceeds max order quantity
     */
    public function test_add_to_cart_when_quantity_exceeds_max_order_qty(): void
    {
        $request = new Request([
            'user_id' => $this->user->id,
            'store_id' => $this->store->id,
            'varient_id' => $this->productVariant->varient_id,
            'qty' => 150, // More than max_ord_qty (100)
        ]);

        $response = $this->controller->add_to_cart($request);

        $this->assertEquals(0, $response['status']);
        $this->assertStringContainsString('quantity between 1 to 100', $response['message']);
    }

    /**
     * Test adding item to cart with deal price
     */
    public function test_add_to_cart_with_deal_price(): void
    {
        DealProduct::factory()->create([
            'varient_id' => $this->productVariant->varient_id,
            'store_id' => $this->store->id,
            'deal_price' => 80,
            'valid_from' => Carbon::now()->subDay(),
            'valid_to' => Carbon::now()->addDay(),
        ]);

        $request = new Request([
            'user_id' => $this->user->id,
            'store_id' => $this->store->id,
            'varient_id' => $this->productVariant->varient_id,
            'qty' => 2,
        ]);

        $response = $this->controller->add_to_cart($request);

        $this->assertEquals(1, $response['status']);
        $this->assertEquals('Cart Updated', $response['message']);
    }

    /**
     * Test adding item to cart when user not found
     */
    public function test_add_to_cart_when_user_not_found(): void
    {
        $request = new Request([
            'user_id' => 999, // Non-existent user
            'store_id' => $this->store->id,
            'varient_id' => $this->productVariant->varient_id,
            'qty' => 2,
        ]);

        $response = $this->controller->add_to_cart($request);

        $this->assertEquals(0, $response['status']);
        $this->assertEquals('User not Found', $response['message']);
    }

    /**
     * Test showing cart items
     */
    public function test_show_cart_with_items(): void
    {
        // Add items to cart
        StoreOrders::factory()->create([
            'store_id' => $this->store->id,
            'store_approval' => $this->user->id,
            'order_cart_id' => 'incart',
            'varient_id' => $this->productVariant->varient_id,
            'qty' => 2,
            'price' => 200,
            'total_mrp' => 240,
            'price_without_tax' => 180.0,
            'tx_price' => 20.0,
            'tx_per' => 10.0,
        ]);

        $request = new Request(['user_id' => $this->user->id]);

        $response = $this->controller->show_cart($request);

        $this->assertEquals(1, $response['status']);
        $this->assertEquals('cart_items', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals(1, $response['data']['total_items']);
    }

    /**
     * Test showing empty cart
     */
    public function test_show_cart_when_empty(): void
    {
        $request = new Request(['user_id' => $this->user->id]);

        $response = $this->controller->show_cart($request);

        $this->assertEquals(0, $response['status']);
        $this->assertEquals('No Items in Cart', $response['message']);
    }

    /**
     * Test deleting item from cart
     */
    public function test_delete_from_cart(): void
    {
        // Add item to cart
        StoreOrders::factory()->create([
            'store_id' => $this->store->id,
            'store_approval' => $this->user->id,
            'order_cart_id' => 'incart',
            'varient_id' => $this->productVariant->varient_id,
            'qty' => 2,
        ]);

        $request = new Request([
            'user_id' => $this->user->id,
            'store_id' => $this->store->id,
            'varient_id' => $this->productVariant->varient_id,
        ]);

        $response = $this->controller->del_frm_cart($request);

        $this->assertEquals(1, $response['status']);
        $this->assertEquals('Product has been removed from cart', $response['message']);
    }

    /**
     * Test checking cart with items from different store
     */
    public function test_check_cart_with_items_from_different_store(): void
    {
        // Create a different store
        $differentStore = Stores::factory()->create([
            'store_name' => 'Different Store',
        ]);

        // Add item to cart from different store
        StoreOrders::factory()->create([
            'store_id' => $differentStore->id,
            'store_approval' => $this->user->id,
            'order_cart_id' => 'incart',
        ]);

        $request = new Request([
            'user_id' => $this->user->id,
            'store_id' => $this->store->id,
        ]);

        $response = $this->controller->check_cart($request);

        $this->assertEquals(1, $response['status']);
        $this->assertStringContainsString('previous cart items will be cleared', $response['message']);
    }

    /**
     * Test checking cart with items from same store
     */
    public function test_check_cart_with_items_from_same_store(): void
    {
        // Add item to cart from same store
        StoreOrders::factory()->create([
            'store_id' => $this->store->id,
            'store_approval' => $this->user->id,
            'order_cart_id' => 'incart',
        ]);

        $request = new Request([
            'user_id' => $this->user->id,
            'store_id' => $this->store->id,
        ]);

        $response = $this->controller->check_cart($request);

        $this->assertEquals(0, $response['status']);
        $this->assertEquals('enter to store', $response['message']);
    }

    /**
     * Test clearing cart
     */
    public function test_clear_cart(): void
    {
        // Add items to cart
        StoreOrders::factory()->create([
            'store_id' => $this->store->id,
            'store_approval' => $this->user->id,
            'order_cart_id' => 'incart',
        ]);

        $request = new Request(['user_id' => $this->user->id]);

        $response = $this->controller->clear_cart($request);

        $this->assertEquals(1, $response['status']);
        $this->assertEquals('your cart has been cleared.', $response['message']);
    }

    /**
     * Test clearing empty cart
     */
    public function test_clear_empty_cart(): void
    {
        $request = new Request(['user_id' => $this->user->id]);

        $response = $this->controller->clear_cart($request);

        $this->assertEquals(0, $response['status']);
        $this->assertEquals('nothing in cart', $response['message']);
    }

    /**
     * Test making an order with valid data
     */
    public function test_make_an_order_with_valid_data(): void
    {
        // Create address in delivery range
        Address::factory()->create([
            'user_id' => $this->user->id,
            'select_status' => 1,
            'lat' => 12.9716,
            'lng' => 77.5946,
            'society_id' => 1,
        ]);

        // Create service area
        ServiceArea::factory()->create([
            'society_id' => 1,
            'store_id' => $this->store->id,
            'delivery_charge' => 50,
        ]);

        // Add items to cart
        StoreOrders::factory()->create([
            'store_id' => $this->store->id,
            'store_approval' => $this->user->id,
            'order_cart_id' => 'incart',
            'varient_id' => $this->productVariant->varient_id,
            'qty' => 2,
            'price' => 200,
            'total_mrp' => 240,
            'price_without_tax' => 180.0,
            'tx_price' => 20.0,
            'tx_per' => 10.0,
        ]);

        $request = new Request([
            'user_id' => $this->user->id,
            'delivery_date' => '2024-01-15',
            'time_slot' => '10:00-12:00',
        ]);

        $response = $this->controller->make_an_order($request);

        $this->assertEquals(1, $response['status']);
        $this->assertEquals('Proceed to payment', $response['message']);
    }

    /**
     * Test making an order with no items in cart
     */
    public function test_make_an_order_with_no_items(): void
    {
        $request = new Request([
            'user_id' => $this->user->id,
            'delivery_date' => '2024-01-15',
            'time_slot' => '10:00-12:00',
        ]);

        $response = $this->controller->make_an_order($request);

        $this->assertEquals(0, $response['status']);
        $this->assertEquals('No Items in cart', $response['message']);
    }

    /**
     * Test making an order with address outside delivery range
     */
    public function test_make_an_order_with_address_outside_range(): void
    {
        $this->markTestIncomplete('Distance calculation needs investigation for SQLite compatibility');

        // Create address outside delivery range (extremely far from store)
        Address::factory()->create([
            'user_id' => $this->user->id,
            'select_status' => 1,
            'lat' => 50.0, // Extremely far from store at 12.9716
            'lng' => 100.0, // Extremely far from store at 77.5946
            'society_id' => 1,
        ]);

        // Add items to cart
        StoreOrders::factory()->create([
            'store_id' => $this->store->id,
            'store_approval' => $this->user->id,
            'order_cart_id' => 'incart',
        ]);

        $request = new Request([
            'user_id' => $this->user->id,
            'delivery_date' => '2024-01-15',
            'time_slot' => '10:00-12:00',
        ]);

        $response = $this->controller->make_an_order($request);

        $this->assertEquals(0, $response['status']);
        $this->assertStringContainsString('address in delivery range', $response['message']);
    }

    /**
     * Test making an order with minimum order value not met
     */
    public function test_make_an_order_below_minimum_value(): void
    {
        // Create minimum order value
        MinimumMaximumOrderValue::factory()->create([
            'store_id' => $this->store->id,
            'min_value' => 500,
            'max_value' => 1000,
        ]);

        // Create address in delivery range
        Address::factory()->create([
            'user_id' => $this->user->id,
            'select_status' => 1,
            'lat' => 12.9716,
            'lng' => 77.5946,
            'society_id' => 1,
        ]);

        // Add items to cart with low value
        StoreOrders::factory()->create([
            'store_id' => $this->store->id,
            'store_approval' => $this->user->id,
            'order_cart_id' => 'incart',
            'varient_id' => $this->productVariant->varient_id,
            'qty' => 1,
            'price' => 100, // Below minimum
            'total_mrp' => 120,
            'price_without_tax' => 90.0,
            'tx_price' => 10.0,
            'tx_per' => 10.0,
        ]);

        $request = new Request([
            'user_id' => $this->user->id,
            'delivery_date' => '2024-01-15',
            'time_slot' => '10:00-12:00',
        ]);

        $response = $this->controller->make_an_order($request);

        $this->assertEquals(0, $response['status']);
        $this->assertStringContainsString('order between 500 to 1000', $response['message']);
    }

    /**
     * Test reordering cart with valid data
     */
    public function test_reorder_cart_with_valid_data(): void
    {
        // Create order
        $order = Orders::factory()->create([
            'cart_id' => 'TEST123',
            'store_id' => $this->store->id,
            'user_id' => $this->user->id,
        ]);

        // Create store orders for the order
        StoreOrders::factory()->create([
            'order_cart_id' => 'TEST123',
            'store_id' => $this->store->id,
            'varient_id' => $this->productVariant->varient_id,
            'qty' => 2,
        ]);

        $request = new Request([
            'cart_id' => 'TEST123',
            'user_id' => $this->user->id,
        ]);

        $response = $this->controller->re_ordercart($request);

        $this->assertEquals(1, $response['status']);
        $this->assertStringContainsString('Added order Products to cart', $response['message']);
    }

    /**
     * Test reordering cart with non-existent cart
     */
    public function test_reorder_cart_with_non_existent_cart(): void
    {
        $request = new Request([
            'cart_id' => 'NONEXISTENT',
            'user_id' => $this->user->id,
        ]);

        $response = $this->controller->re_ordercart($request);

        $this->assertEquals(0, $response['status']);
        $this->assertEquals('No Cart Found', $response['message']);
    }

    /**
     * Test cart calculations with tax
     */
    public function test_cart_calculations_with_tax(): void
    {
        // Add item to cart
        StoreOrders::factory()->create([
            'store_id' => $this->store->id,
            'store_approval' => $this->user->id,
            'order_cart_id' => 'incart',
            'varient_id' => $this->productVariant->varient_id,
            'qty' => 2,
            'price' => 220, // Price with tax
            'total_mrp' => 240,
            'tx_per' => 10,
            'tx_price' => 20,
            'price_without_tax' => 200,
        ]);

        $request = new Request(['user_id' => $this->user->id]);

        $response = $this->controller->show_cart($request);

        $this->assertEquals(1, $response['status']);
        $this->assertArrayHasKey('total_tax', $response['data']);
        $this->assertArrayHasKey('avg_tax', $response['data']);
        $this->assertEquals(20, $response['data']['total_tax']);
        $this->assertEquals(10, $response['data']['avg_tax']);
    }

    /**
     * Test cart with wishlist and rating data
     */
    public function test_cart_with_wishlist_and_rating(): void
    {
        // Add item to cart
        StoreOrders::factory()->create([
            'store_id' => $this->store->id,
            'store_approval' => $this->user->id,
            'order_cart_id' => 'incart',
            'varient_id' => $this->productVariant->varient_id,
        ]);

        // Add wishlist item
        Wishlist::factory()->create([
            'user_id' => $this->user->id,
            'store_id' => $this->store->id,
            'varient_id' => $this->productVariant->varient_id,
        ]);

        // Add product rating
        ProductRating::factory()->create([
            'varient_id' => $this->productVariant->varient_id,
            'store_id' => $this->store->id,
            'user_id' => $this->user->id,
            'rating' => 4,
        ]);

        $request = new Request(['user_id' => $this->user->id]);

        $response = $this->controller->show_cart($request);

        $this->assertEquals(1, $response['status']);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('data', $response['data']);

        $cartItem = $response['data']['data'][0];
        $this->assertEquals('true', $cartItem->isFavourite);
        $this->assertEquals(4, $cartItem->avgrating);
        $this->assertEquals(1, $cartItem->countrating);
    }

    /**
     * Test cart discount calculations
     */
    public function test_cart_discount_calculations(): void
    {
        // Add item to cart with MRP higher than price
        StoreOrders::factory()->create([
            'store_id' => $this->store->id,
            'store_approval' => $this->user->id,
            'order_cart_id' => 'incart',
            'varient_id' => $this->productVariant->varient_id,
            'qty' => 2,
            'price' => 200, // Selling price
            'total_mrp' => 240, // MRP
            'price_without_tax' => 180.0,
            'tx_price' => 20.0,
            'tx_per' => 10.0,
        ]);

        $request = new Request(['user_id' => $this->user->id]);

        $response = $this->controller->show_cart($request);

        $this->assertEquals(1, $response['status']);
        $this->assertArrayHasKey('discountonmrp', $response['data']);
        $this->assertEquals(40, $response['data']['discountonmrp']); // 240 - 200
    }
}
