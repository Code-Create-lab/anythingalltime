<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Controllers\Api\CurrencyController;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CurrencyControllerTest extends TestCase
{
    use WithFaker;

    protected CurrencyController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new CurrencyController;
    }

    /**
     * Test currency retrieval with data
     */
    public function test_currency_with_data(): void
    {
        // Create currency record
        DB::table('currency')->insert([
            'currency_sign' => '$',
            'currency_name' => 'USD',
            'currency_symbol' => '$',
            'currency_code' => 'USD',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = new Request;

        $response = $this->controller->currency($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('currency', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('USD', $response['data']->currency_name);
        $this->assertEquals('$', $response['data']->currency_symbol);
    }

    /**
     * Test currency retrieval without data
     */
    public function test_currency_without_data(): void
    {
        $request = new Request;

        $response = $this->controller->currency($request);

        $this->assertEquals('0', $response['status']);
        $this->assertEquals('No currency Found', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertEmpty($response['data']);
    }

    /**
     * Test gateway settings with data
     */
    public function test_gateway_settings_with_data(): void
    {
        // Create settings records
        DB::table('settings')->insert([
            ['name' => 'paypal_active', 'value' => '1'],
            ['name' => 'razorpay_active', 'value' => '1'],
            ['name' => 'paystack_active', 'value' => '1'],
            ['name' => 'stripe_active', 'value' => '1'],
            ['name' => 'paypal_client_id', 'value' => 'test_paypal_client'],
            ['name' => 'paypal_secret_key', 'value' => 'test_paypal_secret'],
            ['name' => 'razorpay_secret_key', 'value' => 'test_razorpay_secret'],
            ['name' => 'razorpay_key_id', 'value' => 'test_razorpay_key'],
            ['name' => 'stripe_secret_key', 'value' => 'test_stripe_secret'],
            ['name' => 'stripe_publishable_key', 'value' => 'test_stripe_publishable'],
            ['name' => 'stripe_merchant_id', 'value' => 'test_stripe_merchant'],
            ['name' => 'paystack_public_key', 'value' => 'test_paystack_public'],
            ['name' => 'paystack_secret_key', 'value' => 'test_paystack_secret'],
        ]);

        $request = new Request;

        $response = $this->controller->gatewaysettings($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Payment Gateways and Values', $response['message']);
        $this->assertArrayHasKey('razorpay', $response);
        $this->assertArrayHasKey('paypal', $response);
        $this->assertArrayHasKey('stripe', $response);
        $this->assertArrayHasKey('paystack', $response);

        // Test individual gateway data
        $this->assertEquals('1', $response['razorpay']['razorpay_status']);
        $this->assertEquals('test_razorpay_key', $response['razorpay']['razorpay_key']);

        $this->assertEquals('1', $response['paypal']['paypal_status']);
        $this->assertEquals('test_paypal_client', $response['paypal']['paypal_client_id']);

        $this->assertEquals('1', $response['stripe']['stripe_status']);
        $this->assertEquals('test_stripe_secret', $response['stripe']['stripe_secret']);

        $this->assertEquals('1', $response['paystack']['paystack_status']);
        $this->assertEquals('test_paystack_public', $response['paystack']['paystack_public_key']);
    }

    /**
     * Test gateway settings without data
     */
    public function test_gateway_settings_without_data(): void
    {
        $request = new Request;

        $response = $this->controller->gatewaysettings($request);

        // Assert that the response contains the expected error structure
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('0', $response['status']);
        $this->assertArrayHasKey('message', $response);
        $this->assertEquals('Required settings not found', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertEmpty($response['data']);
    }

    /**
     * Test currency data structure
     */
    public function test_currency_data_structure(): void
    {
        // Create currency with specific structure
        DB::table('currency')->insert([
            'currency_sign' => '₹',
            'currency_name' => 'Indian Rupee',
            'currency_symbol' => '₹',
            'currency_code' => 'INR',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = new Request;
        $response = $this->controller->currency($request);

        $this->assertEquals('1', $response['status']);
        $this->assertIsObject($response['data']);

        $currencyData = $response['data'];
        $this->assertIsNumeric($currencyData->id);
        $this->assertGreaterThan(0, $currencyData->id);
        $this->assertEquals('Indian Rupee', $currencyData->currency_name);
        $this->assertEquals('₹', $currencyData->currency_symbol);
        $this->assertEquals('INR', $currencyData->currency_code);
    }

    /**
     * Test multiple currencies (only first one returned)
     */
    public function test_multiple_currencies(): void
    {
        // Insert multiple currencies
        DB::table('currency')->insert([
            [
                'currency_sign' => '$',
                'currency_name' => 'USD',
                'currency_symbol' => '$',
                'currency_code' => 'USD',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'currency_sign' => '€',
                'currency_name' => 'EUR',
                'currency_symbol' => '€',
                'currency_code' => 'EUR',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $request = new Request;
        $response = $this->controller->currency($request);

        $this->assertEquals('1', $response['status']);
        // Should return first currency only
        $this->assertEquals('USD', $response['data']->currency_name);
    }

    /**
     * Test payment gateway status toggle
     */
    public function test_payment_gateway_status_toggle(): void
    {
        // Create settings with disabled gateways
        DB::table('settings')->insert([
            ['name' => 'paypal_active', 'value' => '0'],
            ['name' => 'razorpay_active', 'value' => '0'],
            ['name' => 'paystack_active', 'value' => '0'],
            ['name' => 'stripe_active', 'value' => '0'],
            ['name' => 'paypal_client_id', 'value' => ''],
            ['name' => 'paypal_secret_key', 'value' => ''],
            ['name' => 'razorpay_secret_key', 'value' => ''],
            ['name' => 'razorpay_key_id', 'value' => ''],
            ['name' => 'stripe_secret_key', 'value' => ''],
            ['name' => 'stripe_publishable_key', 'value' => ''],
            ['name' => 'stripe_merchant_id', 'value' => ''],
            ['name' => 'paystack_public_key', 'value' => ''],
            ['name' => 'paystack_secret_key', 'value' => ''],
        ]);

        $request = new Request;
        $response = $this->controller->gatewaysettings($request);

        $this->assertEquals('1', $response['status']);

        // All gateways should be disabled
        $this->assertEquals('0', $response['razorpay']['razorpay_status']);
        $this->assertEquals('0', $response['paypal']['paypal_status']);
        $this->assertEquals('0', $response['stripe']['stripe_status']);
        $this->assertEquals('0', $response['paystack']['paystack_status']);
    }

    /**
     * Test partial gateway configuration
     */
    public function test_partial_gateway_configuration(): void
    {
        // Configure only Razorpay
        DB::table('settings')->insert([
            ['name' => 'paypal_active', 'value' => '0'],
            ['name' => 'razorpay_active', 'value' => '1'],
            ['name' => 'paystack_active', 'value' => '0'],
            ['name' => 'stripe_active', 'value' => '0'],
            ['name' => 'paypal_client_id', 'value' => ''],
            ['name' => 'paypal_secret_key', 'value' => ''],
            ['name' => 'razorpay_secret_key', 'value' => 'live_secret'],
            ['name' => 'razorpay_key_id', 'value' => 'live_key'],
            ['name' => 'stripe_secret_key', 'value' => ''],
            ['name' => 'stripe_publishable_key', 'value' => ''],
            ['name' => 'stripe_merchant_id', 'value' => ''],
            ['name' => 'paystack_public_key', 'value' => ''],
            ['name' => 'paystack_secret_key', 'value' => ''],
        ]);

        $request = new Request;
        $response = $this->controller->gatewaysettings($request);

        $this->assertEquals('1', $response['status']);

        // Only Razorpay should be active
        $this->assertEquals('1', $response['razorpay']['razorpay_status']);
        $this->assertEquals('live_secret', $response['razorpay']['razorpay_secret']);
        $this->assertEquals('live_key', $response['razorpay']['razorpay_key']);

        // Others should be inactive
        $this->assertEquals('0', $response['paypal']['paypal_status']);
        $this->assertEquals('0', $response['stripe']['stripe_status']);
        $this->assertEquals('0', $response['paystack']['paystack_status']);
    }
}
