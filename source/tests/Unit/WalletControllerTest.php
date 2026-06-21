<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Controllers\Api\WalletController;
use App\Models\User;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WalletControllerTest extends TestCase
{
    use WithFaker;

    protected WalletController $controller;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new WalletController;

        // Create test user
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'user_phone' => '1234567890',
            'email' => 'test@example.com',
            'wallet' => 500.00,
        ]);
    }

    /**
     * Test wallet amount retrieval
     */
    public function test_wallet_amount(): void
    {
        $request = new Request([
            'user_id' => $this->user->id,
        ]);

        $response = $this->controller->walletamount($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Wallet_amount', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals(500.00, $response['data']);
    }

    /**
     * Test wallet amount for non-existent user
     */
    public function test_wallet_amount_user_not_found(): void
    {
        $request = new Request([
            'user_id' => 999,
        ]);

        $response = $this->controller->walletamount($request);

        $this->assertEquals('0', $response['status']);
        $this->assertEquals('No user Found', $response['message']);
        $this->assertEquals(0, $response['data']);
    }

    /**
     * Test wallet amount with zero balance
     */
    public function test_wallet_amount_zero_balance(): void
    {
        // Update user wallet to zero
        $this->user->update(['wallet' => 0]);

        $request = new Request([
            'user_id' => $this->user->id,
        ]);

        $response = $this->controller->walletamount($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Wallet_amount', $response['message']);
        $this->assertEquals(0, $response['data']);
    }

    /**
     * Test total bill with no orders
     */
    public function test_total_bill_no_orders(): void
    {
        $request = new Request([
            'user_id' => $this->user->id,
        ]);

        $response = $this->controller->totalbill($request);

        // When no orders exist, it returns status 1 with 0 total
        $this->assertEquals('1', $response['status']);
        $this->assertArrayHasKey('message', $response);
    }

    /**
     * Test show recharge history empty
     */
    public function test_show_recharge_history_empty(): void
    {
        $request = new Request([
            'user_id' => $this->user->id,
        ]);

        $response = $this->controller->show_recharge_history($request);

        // The controller returns 'something went wrong' for empty history
        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('message', $response);
        $this->assertStringContainsString('wrong', $response['message']);
    }

    /**
     * Test wallet data structure consistency
     */
    public function test_wallet_data_structure(): void
    {
        // Test the database query that walletamount uses
        $walletData = DB::table('users')
            ->select('wallet')
            ->where('id', $this->user->id)
            ->first();

        $this->assertNotNull($walletData);
        $this->assertEquals(500.00, $walletData->wallet);
    }

    /**
     * Test user wallet exists in database
     */
    public function test_user_wallet_exists(): void
    {
        $user = User::find($this->user->id);

        $this->assertNotNull($user);
        $this->assertNotNull($user->wallet);
        $this->assertIsNumeric($user->wallet);
        $this->assertEquals(500.00, $user->wallet);
    }

    /**
     * Test wallet update operations
     */
    public function test_wallet_update(): void
    {
        $originalAmount = $this->user->wallet;

        // Manually update wallet (simulating what add_credit does)
        $newAmount = $originalAmount + 100;
        $this->user->update(['wallet' => $newAmount]);

        // Verify update
        $updatedUser = User::find($this->user->id);
        $this->assertEquals($newAmount, $updatedUser->wallet);

        // Test wallet amount retrieval after update
        $request = new Request(['user_id' => $this->user->id]);
        $response = $this->controller->walletamount($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals($newAmount, $response['data']);
    }

    /**
     * Test wallet with different numeric values
     */
    public function test_wallet_different_values(): void
    {
        $testValues = [0, 1.50, 999.99, 1000, 50.25];

        foreach ($testValues as $value) {
            $this->user->update(['wallet' => $value]);

            $request = new Request(['user_id' => $this->user->id]);
            $response = $this->controller->walletamount($request);

            $this->assertEquals('1', $response['status']);
            $this->assertEquals($value, $response['data']);
        }
    }
}
