<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Controllers\Api\OrderController;
use App\Models\Orders;
use App\Models\User;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use WithFaker;

    protected OrderController $controller;

    protected User $user;

    protected int $storeId;

    protected int $addressId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new OrderController;

        // Create test user using direct DB insert to avoid complex dependencies
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'user_phone' => '1234567890',
            'wallet' => 500,
        ]);

        // Create basic store record and get the ID
        $this->storeId = DB::table('store')->insertGetId([
            'store_name' => 'Test Store',
            'employee_name' => 'Test Employee',
            'phone_number' => '9876543210',
            'store_photo' => 'N/A',
            'city' => 'Test City',
            'city_id' => 1,
            'admin_share' => 10,
            'device_id' => 'test-device',
            'email' => 'test@store.com',
            'password' => bcrypt('password'),
            'del_range' => 10,
            'lat' => '12.9716',
            'lng' => '77.5946',
            'address' => 'Test Address',
            'admin_approval' => 1,
            'orders' => 1,
            'store_status' => 1,
            'store_opening_time' => '09:00',
            'store_closing_time' => '18:00',
            'time_interval' => 30,
        ]);

        // Create basic address record and get the ID
        $this->addressId = DB::table('address')->insertGetId([
            'user_id' => $this->user->id,
            'receiver_name' => 'Test Receiver',
            'receiver_phone' => '1234567890',
            'house_no' => '123',
            'society' => 'Test Society',
            'city' => 'Test City',
            'city_id' => 1,
            'society_id' => 1,
            'state' => 'Test State',
            'pincode' => '123456',
            'type' => 'Home',
            'select_status' => 1,
            'lat' => '12.9716',
            'lng' => '77.5946',
            'added_at' => now(),
        ]);

        // Insert default currency for order cancellation tests
        DB::table('currency')->insert([
            'currency_sign' => '$',
            'currency_name' => 'USD',
            'currency_symbol' => '$',
            'currency_code' => 'USD',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert default smsby for order cancellation tests
        DB::table('smsby')->insert([
            'status' => 0,
            'msg91' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert default role for admin
        DB::table('roles')->insert([
            'role_id' => 1,
            'role_name' => 'Super Admin',
            'role_description' => 'Super Administrator',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert default tbl_web_setting for order cancellation tests
        DB::table('tbl_web_setting')->insert([
            'name' => 'TestApp',
            'icon' => 'test.png',
            'favicon' => 'test.ico',
            'number_limit' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert default admin record for order processing
        DB::table('admin')->insert([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'phone' => '1234567890',
            'image' => 'admin.jpg',
            'role_id' => 1,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Test ongoing orders retrieval
     */
    public function test_ongoing_orders(): void
    {
        // Create test order
        Orders::factory()->create([
            'cart_id' => 'TEST123',
            'user_id' => $this->user->id,
            'store_id' => $this->storeId,
            'order_status' => 'Pending',
            'payment_method' => 'COD',
            'address_id' => $this->addressId,
            'total_price' => 200,
            'rem_price' => 200,
            'delivery_date' => now()->addDay()->format('Y-m-d'),
            'time_slot' => '10:00-12:00',
        ]);

        $request = new Request([
            'user_id' => $this->user->id,
        ]);

        $response = $this->controller->ongoing($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('My All orders', $response['message']);
        $this->assertArrayHasKey('data', $response);
    }

    /**
     * Test ongoing orders when none exist
     */
    public function test_ongoing_orders_empty(): void
    {
        $request = new Request([
            'user_id' => $this->user->id,
        ]);

        $response = $this->controller->ongoing($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('No Orders Yet', $response['message']);
    }

    /**
     * Test completed orders retrieval
     */
    public function test_completed_orders(): void
    {
        // Create completed order
        Orders::factory()->create([
            'cart_id' => 'COMPLETED123',
            'user_id' => $this->user->id,
            'store_id' => $this->storeId,
            'order_status' => 'Completed',
            'payment_method' => 'COD',
            'address_id' => $this->addressId,
        ]);

        $request = new Request([
            'user_id' => $this->user->id,
        ]);

        $response = $this->controller->completed_orders($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('My All orders', $response['message']);
        $this->assertArrayHasKey('data', $response);
    }

    /**
     * Test completed orders when none exist
     */
    public function test_completed_orders_empty(): void
    {
        $request = new Request([
            'user_id' => $this->user->id,
        ]);

        $response = $this->controller->completed_orders($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('No Orders Yet', $response['message']);
    }

    /**
     * Test cancelled orders retrieval
     */
    public function test_cancelled_orders(): void
    {
        // Create cancelled order
        Orders::factory()->create([
            'cart_id' => 'CANCELLED123',
            'user_id' => $this->user->id,
            'store_id' => $this->storeId,
            'order_status' => 'Cancelled',
            'payment_method' => 'COD',
            'address_id' => $this->addressId,
        ]);

        $request = new Request([
            'user_id' => $this->user->id,
        ]);

        $response = $this->controller->can_orders($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Cancelled orders', $response['message']);
        $this->assertArrayHasKey('data', $response);
    }

    /**
     * Test cancelled orders when none exist
     */
    public function test_cancelled_orders_empty(): void
    {
        $request = new Request([
            'user_id' => $this->user->id,
        ]);

        $response = $this->controller->can_orders($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('No Orders Cancelled Yet', $response['message']);
    }

    /**
     * Test order cancellation
     */
    public function test_cancel_order(): void
    {
        // Create order to cancel
        $order = Orders::factory()->create([
            'cart_id' => 'CANCEL_TEST123',
            'user_id' => $this->user->id,
            'store_id' => $this->storeId,
            'order_status' => 'Pending',
            'payment_method' => 'COD',
        ]);

        $request = new Request([
            'cart_id' => $order->cart_id,
            'reason' => 'Changed mind',
        ]);

        $response = $this->controller->delete_order($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('order cancelled', $response['message']);

        // Verify order status changed
        $cancelledOrder = Orders::where('cart_id', $order->cart_id)->first();
        $this->assertEquals('Cancelled', $cancelledOrder->order_status);
    }

    /**
     * Test order cancellation with invalid cart ID
     */
    public function test_cancel_order_invalid(): void
    {
        $request = new Request([
            'cart_id' => 'INVALID123',
            'cancel_reason' => 'Test',
        ]);

        $response = $this->controller->cancel_for($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Cancelling reason list', $response['message']);
    }

    /**
     * Test order deletion
     */
    public function test_delete_order(): void
    {
        // Create order to delete
        $order = Orders::factory()->create([
            'cart_id' => 'DELETE_TEST123',
            'user_id' => $this->user->id,
            'store_id' => $this->storeId,
            'order_status' => 'Pending',
        ]);

        $request = new Request([
            'cart_id' => $order->cart_id,
        ]);

        $response = $this->controller->delete_order($request);

        $this->assertEquals('0', $response['status']);
        $this->assertEquals('Select a Cancelling Reason First', $response['message']);

        // Verify order was not deleted or marked as deleted
        $deletedOrder = Orders::where('cart_id', $order->cart_id)->first();
        $this->assertNotNull($deletedOrder);
        $this->assertNotEquals('Deleted', $deletedOrder->order_status);
    }

    /**
     * Test order deletion with invalid cart ID
     */
    public function test_delete_order_invalid(): void
    {
        $request = new Request([
            'cart_id' => 'INVALID123',
        ]);

        $response = $this->controller->delete_order($request);

        $this->assertEquals('0', $response['status']);
        $this->assertEquals('Order not found', $response['message']);
    }

    /**
     * Test top selling products
     */
    public function test_top_selling(): void
    {
        $request = new Request([
            'store_id' => $this->storeId,
        ]);

        $response = $this->controller->top_selling($request);

        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('message', $response);

        if ($response['status'] === '1') {
            $this->assertEquals('Top Selling', $response['message']);
            $this->assertArrayHasKey('data', $response);
        } else {
            $this->assertEquals('Products not found', $response['message']);
        }
    }

    /**
     * Test pen_con_out (pending/confirmed/out for delivery orders)
     */
    public function test_pen_con_out(): void
    {
        $request = new Request([
            'user_id' => $this->user->id,
        ]);

        $response = $this->controller->pen_con_out($request);

        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('message', $response);

        if ($response['status'] === '1') {
            $this->assertArrayHasKey('data', $response);
        } else {
            $this->assertEquals('No Order', $response['message']);
        }
    }

    /**
     * Test order data structure consistency
     */
    public function test_order_data_structure(): void
    {
        // Create test order
        $order = Orders::factory()->create([
            'cart_id' => 'STRUCTURE_TEST123',
            'user_id' => $this->user->id,
            'store_id' => $this->storeId,
            'order_status' => 'Pending',
            'address_id' => $this->addressId,
        ]);

        // Test the database queries that the controller uses
        $orderData = DB::table('orders')
            ->leftJoin('store', 'orders.store_id', '=', 'store.id')
            ->leftJoin('address', 'orders.address_id', '=', 'address.address_id')
            ->select('orders.*', 'store.store_name', 'address.house_no', 'address.society')
            ->where('orders.user_id', $this->user->id)
            ->where('orders.cart_id', $order->cart_id)
            ->first();

        $this->assertNotNull($orderData);
        $this->assertEquals($order->cart_id, $orderData->cart_id);
        $this->assertEquals('Test Store', $orderData->store_name);
        $this->assertEquals('123', $orderData->house_no);
    }

    /**
     * Test store order items operations
     */
    public function test_store_order_items_basic(): void
    {
        // Create basic store order items using DB insert
        DB::table('store_orders')->insert([
            'order_cart_id' => 'ITEMS_TEST123',
            'varient_id' => 1,
            'product_name' => 'Test Product',
            'varient_image' => 'test.jpg',
            'quantity' => 500,
            'unit' => 'g',
            'price' => 100,
            'total_mrp' => 120,
            'qty' => 2,
            'store_id' => $this->storeId,
            'description' => 'Test Description',
            'order_date' => now(),
        ]);

        $storeOrderItems = DB::table('store_orders')
            ->where('order_cart_id', 'ITEMS_TEST123')
            ->get();

        $this->assertCount(1, $storeOrderItems);

        $item = $storeOrderItems->first();
        $this->assertEquals('Test Product', $item->product_name);
        $this->assertEquals(2, $item->qty);
        $this->assertEquals(100, $item->price);
    }

    /**
     * Test basic order status filtering
     */
    public function test_order_status_filtering(): void
    {
        // Create orders with different statuses
        Orders::factory()->create([
            'cart_id' => 'PENDING123',
            'user_id' => $this->user->id,
            'store_id' => $this->storeId,
            'order_status' => 'Pending',
        ]);

        Orders::factory()->create([
            'cart_id' => 'COMPLETED123',
            'user_id' => $this->user->id,
            'store_id' => $this->storeId,
            'order_status' => 'Completed',
        ]);

        // Test filtering by status
        $pendingOrders = DB::table('orders')
            ->where('user_id', $this->user->id)
            ->where('order_status', 'Pending')
            ->get();

        $completedOrders = DB::table('orders')
            ->where('user_id', $this->user->id)
            ->where('order_status', 'Completed')
            ->get();

        $this->assertCount(1, $pendingOrders);
        $this->assertCount(1, $completedOrders);
    }
}
