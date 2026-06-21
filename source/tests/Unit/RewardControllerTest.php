<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Controllers\Api\RewardController;
use App\Models\User;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RewardControllerTest extends TestCase
{
    use WithFaker;

    protected RewardController $controller;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new RewardController;

        // Create test user with rewards
        $this->user = User::factory()->create([
            'rewards' => 100,
            'wallet' => 50.0,
        ]);

        // Create test tables for SQLite
        DB::statement('CREATE TABLE IF NOT EXISTS reedem_values (
            id INTEGER PRIMARY KEY,
            value REAL,
            reward_point INTEGER
        )');

        DB::statement('CREATE TABLE IF NOT EXISTS wallet_recharge_history (
            id INTEGER PRIMARY KEY,
            user_id INTEGER,
            amount REAL,
            date_of_recharge TEXT,
            recharge_status TEXT,
            payment_gateway TEXT
        )');

        DB::statement('CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY,
            cart_id TEXT,
            user_id INTEGER,
            total_price REAL,
            order_status TEXT,
            delivery_date TEXT,
            time_slot TEXT,
            store_id INTEGER
        )');

        DB::statement('CREATE TABLE IF NOT EXISTS currency (
            id INTEGER PRIMARY KEY,
            currency_sign TEXT,
            currency_name TEXT
        )');

        DB::statement('CREATE TABLE IF NOT EXISTS reward_points (
            id INTEGER PRIMARY KEY,
            min_cart_value REAL,
            reward_point INTEGER
        )');

        DB::statement('CREATE TABLE IF NOT EXISTS membership_plan (
            plan_id INTEGER PRIMARY KEY,
            plan_name TEXT,
            reward INTEGER,
            price REAL DEFAULT 0
        )');

        // Clear tables and create redeem values configuration
        DB::table('reedem_values')->delete();
        DB::table('reedem_values')->insert([
            'value' => 1.0,
            'reward_point' => 10,
        ]);
    }

    /**
     * Test successful reward redemption
     */
    public function test_redeem_rewards_successful(): void
    {
        $request = new Request(['user_id' => $this->user->id]);

        $response = $this->controller->redeem($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Rewards Redeemed', $response['message']);

        // Verify user rewards reset to 0 and wallet updated
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'rewards' => 0,
            'wallet' => 60.0, // 50 + (100 * 1.0 / 10)
        ]);

        // Verify wallet recharge history entry
        $this->assertDatabaseHas('wallet_recharge_history', [
            'user_id' => $this->user->id,
            'amount' => 10.0,
            'recharge_status' => 'success',
            'payment_gateway' => 'Rewards',
        ]);
    }

    /**
     * Test reward redemption with no rewards
     */
    public function test_redeem_no_rewards(): void
    {
        // Update user to have no rewards
        DB::table('users')->where('id', $this->user->id)->update(['rewards' => 0]);

        $request = new Request(['user_id' => $this->user->id]);

        $response = $this->controller->redeem($request);

        $this->assertEquals('0', $response['status']);
        $this->assertEquals('You have not get any rewards yet', $response['message']);

        // Verify wallet and rewards unchanged
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'rewards' => 0,
            'wallet' => 50.0,
        ]);
    }

    /**
     * Test reward redemption with negative rewards
     */
    public function test_redeem_negative_rewards(): void
    {
        // Update user to have negative rewards
        DB::table('users')->where('id', $this->user->id)->update(['rewards' => -5]);

        $request = new Request(['user_id' => $this->user->id]);

        $response = $this->controller->redeem($request);

        $this->assertEquals('0', $response['status']);
        $this->assertEquals('You have not get any rewards yet', $response['message']);
    }

    /**
     * Test reward values retrieval with data
     */
    public function test_reward_values_with_data(): void
    {
        $request = new Request;

        $response = $this->controller->rewardvalues($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Rewards Point Values', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals(1.0, $response['data']->value);
        $this->assertEquals(10, $response['data']->reward_point);
    }

    /**
     * Test reward values retrieval without data
     */
    public function test_reward_values_without_data(): void
    {
        // Remove redeem values
        DB::table('reedem_values')->delete();

        $request = new Request;

        $response = $this->controller->rewardvalues($request);

        $this->assertEquals('0', $response['status']);
        $this->assertEquals('Something Went Wrong', $response['message']);
    }

    /**
     * Test reward lines calculation for order
     */
    public function test_reward_lines_basic_calculation(): void
    {
        // Create test order
        DB::table('orders')->insert([
            'cart_id' => 'TEST123',
            'user_id' => $this->user->id,
            'total_price' => 100,
            'order_status' => 'Pending',
            'store_id' => 1,
            'address_id' => 1,
            'price_without_delivery' => 90,
            'total_products_mrp' => 100,
            'order_date' => now()->format('Y-m-d'),
            'delivery_date' => now()->format('Y-m-d'),
            'time_slot' => '10:00 AM - 12:00 PM',
            'rem_price' => 100,
        ]);

        // Create currency
        DB::table('currency')->insert([
            'currency_sign' => '$',
            'currency_name' => 'USD',
        ]);

        // Create reward points tier
        DB::table('reward_points')->insert([
            'min_cart_value' => 50,
            'reward_point' => 5,
        ]);

        $request = new Request(['cart_id' => 'TEST123']);

        $response = $this->controller->rewardlines($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Checkout Rewards lines', $response['message']);
        $this->assertArrayHasKey('line1', $response);
        $this->assertArrayHasKey('line2', $response);
        $this->assertStringContainsString('You will get 5 reward points', $response['line1']);
    }

    /**
     * Test reward lines with membership bonus
     */
    public function test_reward_lines_with_membership(): void
    {
        // Update user with membership
        $expiryDate = date('Y-m-d', strtotime('+30 days'));
        DB::table('users')->where('id', $this->user->id)->update([
            'membership' => 1,
            'mem_plan_expiry' => $expiryDate,
        ]);

        // Create membership plan
        DB::table('membership_plan')->insert([
            'plan_id' => 1,
            'plan_name' => 'Premium',
            'reward' => 2,
            'price' => 100,
            'days' => 30,
            'free_delivery' => 1,
            'instant_delivery' => 0,
        ]);

        // Create test order
        DB::table('orders')->insert([
            'cart_id' => 'TEST456',
            'user_id' => $this->user->id,
            'total_price' => 100,
            'order_status' => 'Pending',
            'store_id' => 1,
            'address_id' => 1,
            'price_without_delivery' => 90,
            'total_products_mrp' => 100,
            'order_date' => now()->format('Y-m-d'),
            'delivery_date' => now()->format('Y-m-d'),
            'time_slot' => '10:00 AM - 12:00 PM',
            'rem_price' => 100,
        ]);

        // Create currency
        DB::table('currency')->insert([
            'currency_sign' => '$',
            'currency_name' => 'USD',
        ]);

        // Create reward points tier
        DB::table('reward_points')->insert([
            'min_cart_value' => 50,
            'reward_point' => 5,
        ]);

        $request = new Request(['cart_id' => 'TEST456']);

        $response = $this->controller->rewardlines($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Checkout Rewards lines', $response['message']);
        $this->assertStringContainsString('You will get 10 reward points', $response['line1']); // 5 * 2 membership multiplier
    }

    /**
     * Test reward lines with next tier suggestion
     */
    public function test_reward_lines_with_next_tier(): void
    {
        // Create test order
        DB::table('orders')->insert([
            'cart_id' => 'TEST789',
            'user_id' => $this->user->id,
            'total_price' => 75,
            'order_status' => 'Pending',
            'store_id' => 1,
            'address_id' => 1,
            'price_without_delivery' => 70,
            'total_products_mrp' => 75,
            'order_date' => now()->format('Y-m-d'),
            'delivery_date' => now()->format('Y-m-d'),
            'time_slot' => '10:00 AM - 12:00 PM',
            'rem_price' => 75,
        ]);

        // Create currency
        DB::table('currency')->insert([
            'currency_sign' => '$',
            'currency_name' => 'USD',
        ]);

        // Create reward points tiers
        DB::table('reward_points')->insert([
            ['min_cart_value' => 50, 'reward_point' => 5],
            ['min_cart_value' => 100, 'reward_point' => 10],
        ]);

        $request = new Request(['cart_id' => 'TEST789']);

        $response = $this->controller->rewardlines($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Checkout Rewards lines', $response['message']);
        $this->assertStringContainsString('You will get 5 reward points', $response['line1']);
        $this->assertStringContainsString('Add items of $ 25 more to get 10 reward points', $response['line2']);
    }

    /**
     * Test reward lines with no applicable rewards
     */
    public function test_reward_lines_no_rewards(): void
    {
        // Create test order with low value
        DB::table('orders')->insert([
            'cart_id' => 'TEST000',
            'user_id' => $this->user->id,
            'total_price' => 10,
            'order_status' => 'Pending',
            'store_id' => 1,
            'address_id' => 1,
            'price_without_delivery' => 8,
            'total_products_mrp' => 10,
            'order_date' => now()->format('Y-m-d'),
            'delivery_date' => now()->format('Y-m-d'),
            'time_slot' => '10:00 AM - 12:00 PM',
            'rem_price' => 10,
        ]);

        // Create reward points tier with high minimum
        DB::table('reward_points')->insert([
            'min_cart_value' => 100,
            'reward_point' => 5,
        ]);

        $request = new Request(['cart_id' => 'TEST000']);

        $response = $this->controller->rewardlines($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('0', $responseData['status']);
        $this->assertEquals('No reward points available', $responseData['message']);
    }

    /**
     * Test different reward calculations
     */
    public function test_reward_calculation_variations(): void
    {
        // Test with different reward values configuration
        DB::table('reedem_values')->delete();
        DB::table('reedem_values')->insert([
            'value' => 0.5,
            'reward_point' => 5,
        ]);

        // Create user with different reward amount
        $user2 = User::factory()->create([
            'rewards' => 50,
            'wallet' => 100.0,
        ]);

        $request = new Request(['user_id' => $user2->id]);

        $response = $this->controller->redeem($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Rewards Redeemed', $response['message']);

        // Verify calculation: 50 * 0.5 / 5 = 5
        $this->assertDatabaseHas('users', [
            'id' => $user2->id,
            'rewards' => 0,
            'wallet' => 105.0, // 100 + 5
        ]);
    }
}
