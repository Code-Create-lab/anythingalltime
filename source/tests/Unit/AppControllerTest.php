<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Controllers\Api\AppController;
use App\Models\Address;
use App\Models\AppLink;
use App\Models\CountryCode;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\FCM;
use App\Models\Firebase;
use App\Models\FirebaseISO;
use App\Models\FreeDeliveryCart;
use App\Models\MembershipBought;
use App\Models\MembershipPlan;
use App\Models\MinimumMaximumOrderValue;
use App\Models\Orders;
use App\Models\ReferralPoints;
use App\Models\SMSBy;
use App\Models\StoreBanner;
use App\Models\StoreOrders;
use App\Models\Stores;
use App\Models\User;
use App\Models\WebSetting;
use App\Models\Wishlist;
use Carbon\Carbon;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AppControllerTest extends TestCase
{
    use WithFaker;

    protected AppController $controller;

    protected User $user;

    protected Stores $store;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new AppController;

        // Create test user and store for tests
        $this->user = User::factory()->create([
            'user_phone' => '1234567890',
            'wallet' => 1000,
        ]);

        $this->store = Stores::factory()->create([
            'store_name' => 'Test Store',
        ]);
    }

    /**
     * Test app configuration with data
     */
    public function test_app_configuration_with_data(): void
    {
        // Create web setting
        WebSetting::factory()->create([
            'name' => 'Test App',
            'icon' => 'test_icon.png',
        ]);

        // User already created in setUp

        // Create FCM settings
        FCM::factory()->create([
            'server_key' => 'test_user_key',
            'store_server_key' => 'test_store_key',
            'driver_server_key' => 'test_driver_key',
        ]);

        // Create Firebase settings
        Firebase::factory()->create([
            'status' => '1',
        ]);

        // Create referral points
        ReferralPoints::factory()->create([
            'points' => json_encode(['min' => 10, 'max' => 100]),
        ]);

        // Create currency
        Currency::factory()->create([
            'currency_sign' => '$',
            'currency_name' => 'USD',
        ]);

        // Create country code
        CountryCode::factory()->create([
            'country_code' => '+1',
        ]);

        // Create Firebase ISO
        FirebaseISO::factory()->create([
            'iso_code' => 'US',
        ]);

        SMSBy::factory()->create([
            'status' => 1,
        ]);

        AppLink::factory()->create([
            'android_app_link' => 'https://play.google.com/test',
            'ios_app_link' => 'https://apps.apple.com/test',
        ]);

        $request = new Request([
            'user_id' => $this->user->id,
            'store_id' => $this->store->id,
        ]);

        $response = $this->controller->app($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('1', $responseData['status']);
        $this->assertEquals('App Name & Logo', $responseData['message']);
        $this->assertEquals('Test App', $responseData['app_name']);
        $this->assertEquals(1000, $responseData['userwallet']);
        $this->assertEquals('on', $responseData['firebase']);
        $this->assertEquals('on', $responseData['sms']);
    }

    /**
     * Test app configuration without data
     */
    public function test_app_configuration_without_data(): void
    {
        $request = new Request([
            'user_id' => $this->user->id,
            'store_id' => $this->store->id,
        ]);

        $response = $this->controller->app($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('0', $responseData['status']);
        $this->assertEquals('Data not found', $responseData['message']);
        $this->assertArrayHasKey('image_url', $responseData);
    }

    /**
     * Test app configuration with wishlist and cart items
     */
    public function test_app_configuration_with_wishlist_cart(): void
    {
        // Create web setting
        WebSetting::factory()->create([
            'name' => 'Test App',
            'icon' => 'test_icon.png',
        ]);

        // Create wishlist items
        Wishlist::factory()->create([
            'user_id' => $this->user->id,
            'store_id' => $this->store->id,
        ]);

        Wishlist::factory()->create([
            'user_id' => $this->user->id,
            'store_id' => $this->store->id,
        ]);

        // Create cart items
        StoreOrders::factory()->create([
            'store_approval' => $this->user->id,
            'order_cart_id' => 'incart',
            'price' => 100,
        ]);

        StoreOrders::factory()->create([
            'store_approval' => $this->user->id,
            'order_cart_id' => 'incart',
            'price' => 200,
        ]);

        $request = new Request([
            'user_id' => $this->user->id,
            'store_id' => $this->store->id,
        ]);

        $response = $this->controller->app($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('1', $responseData['status']);
        $this->assertEquals(2, $responseData['wishlist_count']);
        $this->assertEquals(2, $responseData['total_items']);
    }

    /**
     * Test coupon list with data
     */
    public function test_coupon_list_with_data(): void
    {
        // Create coupons
        Coupon::factory()->create([
            'store_id' => 1,
            'coupon_id' => 1,
        ]);

        Coupon::factory()->create([
            'store_id' => 1,
            'coupon_id' => 2,
        ]);

        // Create order using coupon
        Orders::factory()->create([
            'coupon_id' => 1,
            'user_id' => 1,
            'order_status' => 'Completed',
        ]);

        $request = new Request([
            'store_id' => 1,
            'user_id' => 1,
        ]);

        $response = $this->controller->couponlist($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('1', $responseData['status']);
        $this->assertEquals('Coupon list', $responseData['message']);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals(2, count($responseData['data']));
    }

    /**
     * Test coupon list without data
     */
    public function test_coupon_list_without_data(): void
    {
        $request = new Request([
            'store_id' => 1,
            'user_id' => 1,
        ]);

        $response = $this->controller->couponlist($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('0', $responseData['status']);
        $this->assertEquals('Data not found', $responseData['message']);
    }

    /**
     * Test delivery info with data
     */
    public function test_delivery_info_with_data(): void
    {
        FreeDeliveryCart::factory()->create([
            'min_cart_value' => 500,
            'del_charge' => 50,
        ]);

        $request = new Request;

        $response = $this->controller->delivery_info($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('1', $responseData['status']);
        $this->assertEquals('Delivery fee and cart value', $responseData['message']);
        $this->assertArrayHasKey('data', $responseData);
    }

    /**
     * Test delivery info without data
     */
    public function test_delivery_info_without_data(): void
    {
        $request = new Request;

        $response = $this->controller->delivery_info($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('0', $responseData['status']);
        $this->assertEquals('Data not found', $responseData['message']);
    }

    /**
     * Test store banners with data
     */
    public function test_store_banners_with_data(): void
    {
        StoreBanner::factory()->create([
            'store_id' => 1,
        ]);

        $request = new Request(['store_id' => 1]);

        $response = $this->controller->storebanner($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('1', $responseData['status']);
        $this->assertEquals('Banner List', $responseData['message']);
        $this->assertArrayHasKey('data', $responseData);
    }

    /**
     * Test store banners without data
     */
    public function test_store_banners_without_data(): void
    {
        $request = new Request(['store_id' => 1]);

        $response = $this->controller->storebanner($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('0', $responseData['status']);
        $this->assertEquals('No Banner Found', $responseData['message']);
    }

    /**
     * Test callback request
     */
    public function test_callback_request(): void
    {
        // Create user first
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'user_phone' => '1234567890',
        ]);

        $request = new Request([
            'user_id' => $this->user->id,
            'store_id' => 1,
        ]);

        $response = $this->controller->call($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('1', $responseData['status']);
        $this->assertEquals('Callback requested successfully', $responseData['message']);

        // Verify callback request was created
        $this->assertDatabaseHas('callback_req', [
            'user_id' => $this->user->id,
            'store_id' => 1,
            'processed' => 0,
        ]);
    }

    /**
     * Test callback request with invalid user
     */
    public function test_callback_request_invalid_user(): void
    {
        $request = new Request([
            'user_id' => 999,
            'store_id' => 1,
        ]);

        $response = $this->controller->call($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('0', $responseData['status']);
        $this->assertEquals('User not found', $responseData['message']);
    }

    /**
     * Test minimum/maximum order value with data
     */
    public function test_minmax_with_data(): void
    {
        MinimumMaximumOrderValue::factory()->create([
            'store_id' => 1,
            'min_value' => 100,
            'max_value' => 1000,
        ]);

        $request = new Request(['store_id' => 1]);

        $response = $this->controller->minmax($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('1', $responseData['status']);
        $this->assertEquals('Min/Max Cart Value', $responseData['message']);
        $this->assertArrayHasKey('data', $responseData);
    }

    /**
     * Test minimum/maximum order value without data
     */
    public function test_minmax_without_data(): void
    {
        $request = new Request(['store_id' => 1]);

        $response = $this->controller->minmax($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('0', $responseData['status']);
        $this->assertEquals('Min/Max Cart Value not found', $responseData['message']);
    }

    /**
     * Test membership plans with data
     */
    public function test_membership_plans_with_data(): void
    {
        MembershipPlan::factory()->create([
            'plan_name' => 'Basic Plan',
            'price' => 100,
            'days' => 30,
        ]);

        MembershipPlan::factory()->create([
            'plan_name' => 'Premium Plan',
            'price' => 200,
            'days' => 60,
        ]);

        $request = new Request;

        $response = $this->controller->membership_plan($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('1', $responseData['status']);
        $this->assertEquals('Membership plan', $responseData['message']);
        $this->assertArrayHasKey('data', $responseData);
    }

    /**
     * Test membership plans without data
     */
    public function test_membership_plans_without_data(): void
    {
        $request = new Request;

        $response = $this->controller->membership_plan($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('0', $responseData['status']);
        $this->assertEquals('Data not found', $responseData['message']);
    }

    /**
     * Test membership status with active membership
     */
    public function test_membership_status_active(): void
    {
        // Create membership plan
        $plan = MembershipPlan::factory()->create([
            'plan_name' => 'Basic Plan',
        ]);

        // Create user with active membership
        $user = User::factory()->create([
            'membership' => $plan->plan_id,
            'mem_plan_expiry' => Carbon::now()->addDays(30),
        ]);

        $request = new Request(['user_id' => $user->id]);

        $response = $this->controller->membership_status($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('1', $responseData['status']);
        $this->assertEquals('Membership plan details', $responseData['message']);
        $this->assertEquals('running', $responseData['data']['status']);
    }

    /**
     * Test membership status with expired membership
     */
    public function test_membership_status_expired(): void
    {
        // Create membership plan
        $plan = MembershipPlan::factory()->create([
            'plan_name' => 'Basic Plan',
        ]);

        // Create user with expired membership
        $user = User::factory()->create([
            'membership' => $plan->plan_id,
            'mem_plan_expiry' => Carbon::now()->subDays(30),
        ]);

        $request = new Request(['user_id' => $user->id]);

        $response = $this->controller->membership_status($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('1', $responseData['status']);
        $this->assertEquals('Membership plan details', $responseData['message']);
        $this->assertEquals('expired', $responseData['data']['status']);
    }

    /**
     * Test membership status without membership
     */
    public function test_membership_status_no_membership(): void
    {
        $request = new Request(['user_id' => 1]);

        $response = $this->controller->membership_status($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('0', $responseData['status']);
        $this->assertEquals('No plan bought yet', $responseData['message']);
    }

    /**
     * Test buying membership with wallet
     */
    public function test_buy_membership_with_wallet(): void
    {
        // Create membership plan
        $plan = MembershipPlan::factory()->create([
            'plan_name' => 'Basic Plan',
            'price' => 100,
            'days' => 30,
        ]);

        // Create user with sufficient wallet
        $user = User::factory()->create([
            'wallet' => 1000,
        ]);

        $request = new Request([
            'user_id' => $user->id,
            'plan_id' => $plan->plan_id,
            'buy_status' => 'wallet',
        ]);

        $response = $this->controller->buymember($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('1', $responseData['status']);
        $this->assertEquals('Membership bought successfully.', $responseData['message']);
    }

    /**
     * Test buying membership with insufficient wallet
     */
    public function test_buy_membership_insufficient_wallet(): void
    {
        // Create membership plan
        $plan = MembershipPlan::factory()->create([
            'plan_name' => 'Basic Plan',
            'price' => 2000,
            'days' => 30,
        ]);

        // Create user with insufficient wallet
        $user = User::factory()->create([
            'wallet' => 100,
        ]);

        $request = new Request([
            'user_id' => $user->id,
            'plan_id' => $plan->plan_id,
            'buy_status' => 'wallet',
        ]);

        $response = $this->controller->buymember($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('2', $responseData['status']);
        $this->assertEquals('Your wallet balance is low! Please Recharge', $responseData['message']);
    }

    /**
     * Test buying membership with existing membership
     */
    public function test_buy_membership_existing_membership(): void
    {
        // Create membership plan
        $plan = MembershipPlan::factory()->create([
            'plan_name' => 'Basic Plan',
            'price' => 100,
            'days' => 30,
        ]);

        // Create user with existing membership
        $user = User::factory()->create([
            'wallet' => 1000,
            'membership' => $plan->plan_id,
            'mem_plan_expiry' => Carbon::now()->addDays(30),
        ]);

        // Create MembershipBought record for the user
        \App\Models\MembershipBought::create([
            'user_id' => $user->id,
            'mem_id' => $plan->plan_id,
            'mem_start_date' => Carbon::now()->subDays(10),
            'mem_end_date' => Carbon::now()->addDays(30),
            'price' => $plan->price,
            'buy_date' => Carbon::now(),
            'paid_by' => 'wallet',
            'transaction_id' => 'TXN123',
            'payment_gateway' => 'wallet',
            'payment_status' => 'wallet',
        ]);

        $request = new Request([
            'user_id' => $user->id,
            'plan_id' => $plan->plan_id,
            'buy_status' => 'wallet',
        ]);

        $response = $this->controller->buymember($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('5', $responseData['status']);
        $this->assertEquals('You have an ongoing membership you cannot buy another till its expiry', $responseData['message']);
    }

    /**
     * Test buying membership with invalid plan
     */
    public function test_buy_membership_invalid_plan(): void
    {
        $request = new Request([
            'user_id' => 1,
            'plan_id' => 999,
            'buy_status' => 'wallet',
        ]);

        $response = $this->controller->buymember($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('0', $responseData['status']);
        $this->assertEquals('Plan not found', $responseData['message']);
    }

    /**
     * Test generating hash
     */
    public function test_generate_hash(): void
    {
        $request = new Request([
            'merchant_secret' => 'test_secret',
            'ord_mercID' => 'test_merchant',
            'ord_mercref' => 'test_ref',
            'amount' => '100',
        ]);

        $response = $this->controller->genhash($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('1', $responseData['status']);
        $this->assertEquals('sha1 generated', $responseData['message']);
        $this->assertArrayHasKey('data', $responseData);

        // Verify hash was generated correctly
        $expectedHash = hash('sha256', 'test_secrettest_merchanttest_ref100');
        $this->assertEquals($expectedHash, $responseData['data']);
    }

    /**
     * Test deleting all users
     */
    public function test_delete_users(): void
    {
        // Create some test data
        User::factory()->create(['id' => 2]);
        Orders::factory()->create(['user_id' => 1]);
        StoreOrders::factory()->create(['store_approval' => 1]);

        $request = new Request;

        $response = $this->controller->delete_users($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('1', $responseData['status']);
        $this->assertEquals('Deleted', $responseData['message']);

        // Verify all users were deleted
        $this->assertEquals(0, User::count());
    }

    /**
     * Test deleting users when none exist
     */
    public function test_delete_users_none_exist(): void
    {
        // Delete all users first
        User::query()->delete();

        $request = new Request;

        $response = $this->controller->delete_users($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('0', $responseData['status']);
        $this->assertEquals('No user found', $responseData['message']);
    }

    /**
     * Test tracking order
     */
    public function test_track_order(): void
    {
        // Create address
        $address = Address::factory()->create([
            'user_id' => 1,
            'house_no' => '123',
            'society' => 'Test Society',
            'city' => 'Test City',
            'landmark' => 'Near Park',
            'state' => 'Test State',
            'pincode' => '123456',
            'lat' => '12.9716',
            'lng' => '77.5946',
        ]);

        // Create store
        $this->store = Stores::factory()->create([
            'store_name' => 'Test Store',
        ]);

        // Create order
        $order = Orders::factory()->create([
            'cart_id' => 'TEST123',
            'user_id' => 1,
            'store_id' => 1,
            'address_id' => $address->address_id,
            'order_status' => 'pending',
        ]);

        $request = new Request(['cart_id' => 'TEST123']);

        $response = $this->controller->trackorder($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('1', $responseData['status']);
        $this->assertEquals('Track order details', $responseData['message']);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals('true', $responseData['data']['pending']);
        $this->assertEquals('false', $responseData['data']['confirm']);
    }

    /**
     * Test tracking non-existent order
     */
    public function test_track_nonexistent_order(): void
    {
        $request = new Request(['cart_id' => 'NONEXISTENT']);

        $response = $this->controller->trackorder($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('0', $responseData['status']);
        $this->assertEquals('Order not found', $responseData['message']);
    }

    /**
     * Test tracking order with different statuses
     */
    public function test_track_order_different_statuses(): void
    {
        // Create address
        $address = Address::factory()->create([
            'user_id' => 1,
            'house_no' => '123',
            'society' => 'Test Society',
            'city' => 'Test City',
            'landmark' => 'Near Park',
            'state' => 'Test State',
            'pincode' => '123456',
            'lat' => '12.9716',
            'lng' => '77.5946',
        ]);

        // Create store
        $this->store = Stores::factory()->create([
            'store_name' => 'Test Store',
        ]);

        // Test completed order
        $order = Orders::factory()->create([
            'cart_id' => 'TEST123',
            'user_id' => 1,
            'store_id' => 1,
            'address_id' => $address->address_id,
            'order_status' => 'completed',
        ]);

        $request = new Request(['cart_id' => 'TEST123']);

        $response = $this->controller->trackorder($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('1', $responseData['status']);
        $this->assertEquals('true', $responseData['data']['completed']);
        $this->assertEquals('false', $responseData['data']['cancelled']);
    }
}
