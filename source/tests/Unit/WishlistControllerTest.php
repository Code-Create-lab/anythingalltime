<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Controllers\Api\WishlistController;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StoreProducts;
use App\Models\Stores;
use App\Models\User;
use App\Models\Wishlist;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WishlistControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected WishlistController $controller;

    protected User $user;

    protected Stores $store;

    protected Category $category;

    protected Product $product;

    protected ProductVariant $productVariant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new WishlistController;

        // Create test user
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'user_phone' => '1234567890',
            'email' => 'test@example.com',
        ]);

        // Create test store using factory
        $this->store = Stores::factory()->create([
            'store_name' => 'Test Store',
            'employee_name' => 'Test Employee',
            'phone_number' => '9876543210',
            'email' => 'test@store.com',
            'lat' => '12.9716',
            'lng' => '77.5946',
            'del_range' => 10,
        ]);

        // Create category
        $this->category = Category::factory()->create([
            'title' => 'Test Category',
        ]);

        // Create product
        $this->product = Product::factory()->create([
            'cat_id' => $this->category->cat_id,
            'product_name' => 'Test Product',
        ]);

        // Create product variant
        $this->productVariant = ProductVariant::factory()->create([
            'product_id' => $this->product->product_id,
            'quantity' => 500,
            'unit' => 'g',
            'description' => 'Test Description',
        ]);

        // Create store product
        StoreProducts::factory()->create([
            'store_id' => $this->store->id,
            'varient_id' => $this->productVariant->varient_id,
            'price' => 100,
            'mrp' => 120,
            'stock' => 50,
            'max_ord_qty' => 10,
        ]);
    }

    /**
     * Test adding item to wishlist
     */
    public function test_add_to_wishlist(): void
    {
        $request = new Request([
            'user_id' => $this->user->id,
            'varient_id' => $this->productVariant->varient_id,
            'store_id' => $this->store->id,
        ]);

        $response = $this->controller->add_to_wishlist($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Added to Wishlist', $response['message']);

        // Verify item was added to wishlist
        $wishlistItem = Wishlist::where('user_id', $this->user->id)
            ->where('varient_id', $this->productVariant->varient_id)
            ->first();

        $this->assertNotNull($wishlistItem);
    }

    /**
     * Test adding duplicate item to wishlist
     */
    public function test_add_duplicate_to_wishlist(): void
    {
        // First add item to wishlist
        Wishlist::factory()->create([
            'user_id' => $this->user->id,
            'varient_id' => $this->productVariant->varient_id,
            'store_id' => $this->store->id,
        ]);

        $request = new Request([
            'user_id' => $this->user->id,
            'varient_id' => $this->productVariant->varient_id,
            'store_id' => $this->store->id,
        ]);

        $response = $this->controller->add_to_wishlist($request);

        $this->assertEquals('2', $response['status']);
        $this->assertEquals('Removed from Wishlist', $response['message']);
    }

    /**
     * Test removing item from wishlist
     */
    public function test_remove_from_wishlist(): void
    {
        // First add item to wishlist
        $wishlistItem = Wishlist::factory()->create([
            'user_id' => $this->user->id,
            'varient_id' => $this->productVariant->varient_id,
            'store_id' => $this->store->id,
        ]);

        $request = new Request([
            'user_id' => $this->user->id,
            'varient_id' => $this->productVariant->varient_id,
            'store_id' => $this->store->id,
        ]);

        $response = $this->controller->add_to_wishlist($request);

        $this->assertEquals('2', $response['status']);
        $this->assertEquals('Removed from Wishlist', $response['message']);

        // Verify item was removed from wishlist
        $deletedItem = Wishlist::find($wishlistItem->wish_id);
        $this->assertNull($deletedItem);
    }

    /**
     * Test show wishlist with items
     */
    public function test_show_wishlist_with_items(): void
    {
        // Add items to wishlist
        Wishlist::factory()->create([
            'user_id' => $this->user->id,
            'varient_id' => $this->productVariant->varient_id,
            'store_id' => $this->store->id,
        ]);

        $request = new Request([
            'user_id' => $this->user->id,
            'store_id' => $this->store->id,
        ]);

        $response = $this->controller->show_wishlist($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Wishlist items', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertCount(1, $response['data']);
    }

    /**
     * Test show wishlist when empty
     */
    public function test_show_wishlist_empty(): void
    {
        $request = new Request([
            'user_id' => $this->user->id,
            'store_id' => $this->store->id,
        ]);

        $response = $this->controller->show_wishlist($request);

        $this->assertEquals('0', $response['status']);
        $this->assertEquals('Nothing in Wishlist From This Location', $response['message']);
    }

    /**
     * Test wishlist to cart functionality
     */
    public function test_wishlist_to_cart(): void
    {
        // Add item to wishlist first
        Wishlist::factory()->create([
            'user_id' => $this->user->id,
            'varient_id' => $this->productVariant->varient_id,
            'store_id' => $this->store->id,
        ]);

        $request = new Request([
            'user_id' => $this->user->id,
            'varient_id' => $this->productVariant->varient_id,
            'store_id' => $this->store->id,
            'quantity' => 2,
        ]);

        $response = $this->controller->wishlist_to_cart($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Added to cart', $response['message']);

        // Verify item was removed from wishlist
        $wishlistItem = Wishlist::where('user_id', $this->user->id)
            ->where('varient_id', $this->productVariant->varient_id)
            ->first();
        $this->assertNull($wishlistItem);
    }

    /**
     * Test wishlist to cart with invalid item
     */
    public function test_wishlist_to_cart_invalid_item(): void
    {
        $request = new Request([
            'user_id' => $this->user->id,
            'varient_id' => 999,
            'store_id' => $this->store->id,
            'quantity' => 2,
        ]);

        $response = $this->controller->wishlist_to_cart($request);

        $this->assertEquals('0', $response['status']);
        $this->assertEquals('All wishlisted items is out of stock', $response['message']);
    }

    /**
     * Test wishlist data structure
     */
    public function test_wishlist_data_structure(): void
    {
        // Create wishlist item
        $wishlistItem = Wishlist::factory()->create([
            'user_id' => $this->user->id,
            'varient_id' => $this->productVariant->varient_id,
            'store_id' => $this->store->id,
        ]);

        // Test the database query structure
        $wishlistData = DB::table('wishlist')
            ->leftJoin('product_varient', 'wishlist.varient_id', '=', 'product_varient.varient_id')
            ->leftJoin('product', 'product_varient.product_id', '=', 'product.product_id')
            ->select('wishlist.*', 'product_varient.base_price as price', 'product.product_name')
            ->where('wishlist.user_id', $this->user->id)
            ->first();

        $this->assertNotNull($wishlistData);
        $this->assertEquals($this->user->id, $wishlistData->user_id);
        $this->assertEquals($this->productVariant->varient_id, $wishlistData->varient_id);
        $this->assertEquals('Test Product', $wishlistData->product_name);
        // Price comes from the ProductVariant factory, so verify it exists and is positive
        $this->assertGreaterThan(0, $wishlistData->price);
    }

    /**
     * Test multiple wishlist items for same user
     */
    public function test_multiple_wishlist_items(): void
    {
        // Create multiple product variants
        $variant2Id = DB::table('product_varient')->insertGetId([
            'product_id' => $this->product->product_id,
            'quantity' => 1000,
            'unit' => 'kg',
            'base_price' => 200,
            'description' => 'Test Description 2',
            'varient_image' => 'test2.jpg',
            'approved' => 1,
        ]);

        // Create store product for variant 2
        DB::table('store_products')->insert([
            'store_id' => $this->store->id,
            'varient_id' => $variant2Id,
            'price' => 200,
            'mrp' => 240,
            'stock' => 30,
            'max_ord_qty' => 5,
        ]);

        // Add multiple items to wishlist
        Wishlist::factory()->create([
            'user_id' => $this->user->id,
            'varient_id' => $this->productVariant->varient_id,
            'store_id' => $this->store->id,
        ]);

        Wishlist::factory()->create([
            'user_id' => $this->user->id,
            'varient_id' => $variant2Id,
            'store_id' => $this->store->id,
        ]);

        $request = new Request([
            'user_id' => $this->user->id,
            'store_id' => $this->store->id,
        ]);

        $response = $this->controller->show_wishlist($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Wishlist items', $response['message']);
        $this->assertCount(2, $response['data']);
    }

    /**
     * Test wishlist operations for different users
     */
    public function test_wishlist_user_isolation(): void
    {
        // Create another user
        $otherUser = User::factory()->create([
            'name' => 'Other User',
            'user_phone' => '9876543210',
        ]);

        // Add items to wishlist for both users
        Wishlist::factory()->create([
            'user_id' => $this->user->id,
            'varient_id' => $this->productVariant->varient_id,
            'store_id' => $this->store->id,
        ]);

        Wishlist::factory()->create([
            'user_id' => $otherUser->id,
            'varient_id' => $this->productVariant->varient_id,
            'store_id' => $this->store->id,
        ]);

        // Test first user's wishlist
        $request1 = new Request([
            'user_id' => $this->user->id,
            'store_id' => $this->store->id,
        ]);

        $response1 = $this->controller->show_wishlist($request1);
        $this->assertEquals('1', $response1['status']);
        $this->assertCount(1, $response1['data']);

        // Test second user's wishlist
        $request2 = new Request([
            'user_id' => $otherUser->id,
            'store_id' => $this->store->id,
        ]);

        $response2 = $this->controller->show_wishlist($request2);
        $this->assertEquals('1', $response2['status']);
        $this->assertCount(1, $response2['data']);
    }
}
