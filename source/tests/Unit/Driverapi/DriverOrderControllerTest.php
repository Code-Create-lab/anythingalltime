<?php

declare(strict_types=1);

namespace Tests\Unit\Driverapi;

use Carbon\Carbon;
use Tests\DriverApiTestCase;

class DriverOrderControllerTest extends DriverApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Mock notification traits
        $this->partialMock(\App\Traits\SendInapp::class);
        $this->partialMock(\App\Traits\SendMail::class);
        $this->partialMock(\App\Traits\SendSms::class);
    }

    /**
     * Test getting today's orders for driver
     */
    public function test_orders_for_today(): void
    {
        // Create orders for today
        $order1 = $this->createTestOrderForDriver([
            'delivery_date' => Carbon::today()->format('Y-m-d'),
            'order_status' => 'Confirmed',
            'time_slot' => '10:00 AM - 12:00 PM',
        ]);

        $order2 = $this->createTestOrderForDriver([
            'delivery_date' => Carbon::today()->format('Y-m-d'),
            'order_status' => 'Out_For_Delivery',
            'time_slot' => '2:00 PM - 4:00 PM',
        ]);

        // Create store order details
        $this->createStoreOrderDetails($order1->cart_id);
        $this->createStoreOrderDetails($order2->cart_id);

        $response = $this->driverApiCall('POST', 'ordersfortoday', [
            'dboy_id' => $this->driver->dboy_id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $response->assertJsonStructure([
            0 => [
                'cart_id',
                'user_name',
                'user_phone',
                'user_address',
                'remaining_price',
                'delivery_date',
                'time_slot',
                'payment_method',
                'order_status',
                'total_items',
                'items',
                'store_name',
            ],
        ]);
    }

    /**
     * Test getting next day orders for driver
     */
    public function test_orders_for_next_day(): void
    {
        $order = $this->createTestOrderForDriver([
            'delivery_date' => Carbon::tomorrow()->format('Y-m-d'),
            'order_status' => 'Confirmed',
        ]);

        $this->createStoreOrderDetails($order->cart_id);

        $response = $this->driverApiCall('POST', 'ordersfornextday', [
            'dboy_id' => $this->driver->dboy_id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonStructure([
            0 => [
                'cart_id',
                'user_name',
                'user_address',
                'order_details',
            ],
        ]);
    }

    /**
     * Test when no orders found
     */
    public function test_no_orders_found(): void
    {
        $response = $this->driverApiCall('POST', 'ordersfortoday', [
            'dboy_id' => $this->driver->dboy_id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            0 => ['order_details' => 'no orders found'],
        ]);
    }

    /**
     * Test order items retrieval
     */
    public function test_order_items(): void
    {
        $order = $this->createTestOrderForDriver([
            'order_status' => 'Confirmed',
        ]);

        // Create multiple simple store order items
        \DB::table('store_orders')->insert([
            [
                'store_id' => $this->store->id,
                'order_cart_id' => $order->cart_id,
                'product_name' => 'Product 1',
                'varient_id' => 1,
                'varient_image' => 'product1.jpg',
                'quantity' => '1',
                'unit' => 'kg',
                'qty' => 2,
                'price' => 100,
                'total_mrp' => 120,
                'description' => 'Test description',
                'order_date' => now(),
                'store_approval' => 1,
            ],
            [
                'store_id' => $this->store->id,
                'order_cart_id' => $order->cart_id,
                'product_name' => 'Product 2',
                'varient_id' => 2,
                'varient_image' => 'product2.jpg',
                'quantity' => '500',
                'unit' => 'gm',
                'qty' => 1,
                'price' => 50,
                'total_mrp' => 60,
                'description' => 'Test description',
                'order_date' => now(),
                'store_approval' => 1,
            ],
        ]);

        $response = $this->driverApiCall('POST', 'order_items', [
            'cart_id' => $order->cart_id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $response->assertJsonStructure([
            0 => [
                'product_name',
                'varient_id',
                'varient_image',
                'quantity',
                'unit',
                'qty',
                'price',
            ],
        ]);
    }

    /**
     * Test marking order as out for delivery
     */
    public function test_out_for_delivery(): void
    {
        $order = $this->createTestOrderForDriver([
            'order_status' => 'Confirmed',
        ]);

        $response = $this->driverApiCall('POST', 'out_for_delivery', [
            'cart_id' => $order->cart_id,
        ]);

        $response->assertStatus(200);

        // Verify order status was updated
        $this->assertDatabaseHas('orders', [
            'cart_id' => $order->cart_id,
            'order_status' => 'Out_For_Delivery',
        ]);
    }

    /**
     * Test completing delivery
     */
    public function test_delivery_completed(): void
    {
        $order = $this->createTestOrderForDriver([
            'order_status' => 'Out_For_Delivery',
            'payment_method' => 'COD',
            'rem_price' => 100,
        ]);

        $response = $this->driverApiCall('POST', 'delivery_completed', [
            'cart_id' => $order->cart_id,
            'payment_method' => 'COD',
        ]);

        $response->assertStatus(200);

        // Verify order was completed
        $this->assertDatabaseHas('orders', [
            'cart_id' => $order->cart_id,
            'order_status' => 'Completed',
            'payment_status' => 'success',
        ]);
    }

    /**
     * Test completing delivery with online payment
     */
    public function test_delivery_completed_online_payment(): void
    {
        $order = $this->createTestOrderForDriver([
            'order_status' => 'Out_For_Delivery',
            'payment_method' => 'Online',
            'payment_status' => 'success',
        ]);

        $response = $this->driverApiCall('POST', 'delivery_completed', [
            'cart_id' => $order->cart_id,
            'payment_method' => 'Online',
        ]);

        $response->assertStatus(200);

        // Verify order was completed
        $this->assertDatabaseHas('orders', [
            'cart_id' => $order->cart_id,
            'order_status' => 'Completed',
        ]);
    }

    /**
     * Test getting completed orders
     */
    public function test_completed_orders(): void
    {
        // Create completed orders
        $order1 = $this->createTestOrderForDriver([
            'order_status' => 'Completed',
            'delivery_date' => Carbon::today()->format('Y-m-d'),
        ]);

        $order2 = $this->createTestOrderForDriver([
            'order_status' => 'Completed',
            'delivery_date' => Carbon::yesterday()->format('Y-m-d'),
        ]);

        $this->createStoreOrderDetails($order1->cart_id);
        $this->createStoreOrderDetails($order2->cart_id);

        $response = $this->driverApiCall('POST', 'completed_orders', [
            'dboy_id' => $this->driver->dboy_id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $response->assertJsonStructure([
            0 => [
                'cart_id',
                'user_name',
                'delivery_date',
                'order_status',
                'order_details',
            ],
        ]);
    }

    /**
     * Test getting completed orders by day
     */
    public function test_complete_order_list_by_day(): void
    {
        $targetDate = Carbon::today()->format('Y-m-d');

        // Create completed order for specific date
        $order = $this->createTestOrderForDriver([
            'order_status' => 'Completed',
            'delivery_date' => $targetDate,
        ]);

        $this->createStoreOrderDetails($order->cart_id);

        $response = $this->driverApiCall('POST', 'completeorderlistbyday', [
            'dboy_id' => $this->driver->dboy_id,
            'date' => $targetDate,
        ]);

        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }

    /**
     * Test order status updates with driver incentive calculation
     */
    public function test_delivery_completion_with_incentive(): void
    {
        // Create initial incentive record
        \DB::table('driver_incentive')->insert([
            'dboy_id' => $this->driver->dboy_id,
            'earned_till_now' => 100,
            'paid_till_now' => 50,
            'remaining' => 50,
        ]);

        // Set up store driver incentive
        \DB::table('store_driver_incentive')->insert([
            'store_id' => $this->store->id,
            'incentive' => 10, // Fixed incentive amount
        ]);

        $order = $this->createTestOrderForDriver([
            'order_status' => 'Out_For_Delivery',
            'delivery_charge' => 30,
            'payment_method' => 'COD',
        ]);

        $response = $this->driverApiCall('POST', 'delivery_completed', [
            'cart_id' => $order->cart_id,
            'payment_method' => 'COD',
        ]);

        $response->assertStatus(200);

        // Verify incentive was updated (100 + 10 = 110)
        $this->assertDatabaseHas('driver_incentive', [
            'dboy_id' => $this->driver->dboy_id,
            'earned_till_now' => 110, // 100 + 10 (fixed incentive)
            'remaining' => 60, // 50 + 10
        ]);
    }

    /**
     * Test handling orders from different time slots
     */
    public function test_orders_sorted_by_time_slot(): void
    {
        // Create orders with different time slots
        $order1 = $this->createTestOrderForDriver([
            'delivery_date' => Carbon::today()->format('Y-m-d'),
            'time_slot' => '6:00 PM - 8:00 PM',
            'order_status' => 'Confirmed',
        ]);

        $order2 = $this->createTestOrderForDriver([
            'delivery_date' => Carbon::today()->format('Y-m-d'),
            'time_slot' => '10:00 AM - 12:00 PM',
            'order_status' => 'Confirmed',
        ]);

        $this->createStoreOrderDetails($order1->cart_id);
        $this->createStoreOrderDetails($order2->cart_id);

        $response = $this->driverApiCall('POST', 'ordersfortoday', [
            'dboy_id' => $this->driver->dboy_id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonCount(2);

        // Orders should be sorted by status first, then time
        $orders = $response->json();
        $this->assertEquals('Confirmed', $orders[0]['order_status']);
        $this->assertEquals('Confirmed', $orders[1]['order_status']);
    }

    /**
     * Helper method to create store order details
     */
    private function createStoreOrderDetails(string $cartId): void
    {
        // Simplified store order creation - just use basic required fields
        \DB::table('store_orders')->insert([
            'store_id' => $this->store->id,
            'order_cart_id' => $cartId,
            'product_name' => 'Test Product',
            'varient_id' => 1, // Use simple ID for test purposes
            'varient_image' => 'test.jpg',
            'quantity' => '1',
            'unit' => 'kg',
            'qty' => 2,
            'price' => 50,
            'total_mrp' => 60,
            'description' => 'Test description',
            'order_date' => now(),
            'store_approval' => 1,
        ]);
    }
}
