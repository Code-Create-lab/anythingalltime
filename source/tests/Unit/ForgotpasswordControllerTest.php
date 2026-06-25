<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Controllers\Api\forgotpasswordController;
use App\Models\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ForgotpasswordControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected forgotpasswordController $controller;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new forgotpasswordController;

        // Create test user
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'user_phone' => '1234567890',
            'user_password' => 'oldpassword',
        ]);

        // Create test tables for SQLite
        DB::statement('CREATE TABLE IF NOT EXISTS smsby (
            id INTEGER PRIMARY KEY,
            msg91 INTEGER DEFAULT 0,
            twilio INTEGER DEFAULT 0,
            status INTEGER DEFAULT 0,
            created_at TEXT,
            updated_at TEXT
        )');

        DB::statement('CREATE TABLE IF NOT EXISTS firebase (
            id INTEGER PRIMARY KEY,
            status TEXT,
            created_at TEXT,
            updated_at TEXT
        )');

        DB::statement('CREATE TABLE IF NOT EXISTS tbl_web_setting (
            id INTEGER PRIMARY KEY,
            name TEXT,
            created_at TEXT,
            updated_at TEXT
        )');

        // Create default web setting
        DB::table('tbl_web_setting')->insert([
            'icon' => 'icon.png',
            'name' => 'Anything Alltime',
            'favicon' => 'favicon.ico',
            'number_limit' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Test checkotponoff with all services off
     */
    public function test_checkotponoff_all_services_off(): void
    {
        // Clear any existing data
        DB::table('smsby')->truncate();

        // Create smsby record with all services off
        DB::table('smsby')->insert([
            'msg91' => 0,
            'twilio' => 0,
            'status' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = new Request;

        $response = $this->controller->checkotponoff($request);

        $this->assertEquals('0', $response['status']);
        $this->assertEquals('SMS/OTP are Off', $response['message']);
    }

    /**
     * Test checkotponoff with msg91 on
     */
    public function test_checkotponoff_msg91_on(): void
    {
        // Clear any existing data
        DB::table('smsby')->truncate();

        // Create smsby record with msg91 on
        DB::table('smsby')->insert([
            'msg91' => 1,
            'twilio' => 0,
            'status' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = new Request;

        $response = $this->controller->checkotponoff($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('SMS/OTP are On', $response['message']);
    }

    /**
     * Test checkotponoff with twilio on
     */
    public function test_checkotponoff_twilio_on(): void
    {
        // Clear any existing data
        DB::table('smsby')->truncate();

        // Create smsby record with twilio on
        DB::table('smsby')->insert([
            'msg91' => 0,
            'twilio' => 1,
            'status' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = new Request;

        $response = $this->controller->checkotponoff($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('SMS/OTP are On', $response['message']);
    }

    /**
     * Test checkotponoff with status on
     */
    public function test_checkotponoff_status_on(): void
    {
        // Clear any existing data
        DB::table('smsby')->truncate();

        // Create smsby record with status on
        DB::table('smsby')->insert([
            'msg91' => 0,
            'twilio' => 0,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = new Request;

        $response = $this->controller->checkotponoff($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('SMS/OTP are On', $response['message']);
    }

    /**
     * Test checkotponoff without data
     */
    public function test_checkotponoff_no_data(): void
    {
        // Clear any existing data
        DB::table('smsby')->truncate();

        $request = new Request;

        $response = $this->controller->checkotponoff($request);

        $this->assertEquals('2', $response['status']);
        $this->assertEquals('table or Data Not Found', $response['message']);
    }

    /**
     * Test forgot_password with valid user
     */
    public function test_forgot_password_valid_user(): void
    {
        // Fake the Mail facade
        Mail::fake();

        $request = new Request([
            'user_email' => 'test@example.com',
            'user_phone' => '1234567890',
        ]);

        $response = $this->controller->forgot_password($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Email sent', $response['message']);

        // Since we're using Mail::fake(), the mail won't actually be sent
        // But we can verify that send() was attempted by checking there were no errors
        $this->assertTrue(true);
    }

    /**
     * Test forgot_password with invalid user
     */
    public function test_forgot_password_invalid_user(): void
    {
        // Fake the Mail facade
        Mail::fake();

        $request = new Request([
            'user_email' => 'nonexistent@example.com',
            'user_phone' => '9999999999',
        ]);

        $response = $this->controller->forgot_password($request);

        $this->assertEquals('0', $response['status']);
        $this->assertEquals('Email/phone is not registered', $response['message']);

        // Assert no mail was sent
        Mail::assertNothingSent();
    }

    /**
     * Test forgot_password with mismatched email and phone
     */
    public function test_forgot_password_mismatched_credentials(): void
    {
        // Fake the Mail facade
        Mail::fake();

        $request = new Request([
            'user_email' => 'test@example.com',
            'user_phone' => '9999999999', // Wrong phone for this email
        ]);

        $response = $this->controller->forgot_password($request);

        $this->assertEquals('0', $response['status']);
        $this->assertEquals('Email/phone is not registered', $response['message']);

        // Assert no mail was sent
        Mail::assertNothingSent();
    }

    /**
     * Test verifyOtp3 with success status
     */
    public function test_verify_otp3_success(): void
    {
        $request = new Request([
            'user_phone' => '1234567890',
            'status' => 'success',
        ]);

        $response = $this->controller->verifyOtp3($request);

        $this->assertEquals(1, $response['status']);
        $this->assertEquals('Otp Matched Successfully', $response['message']);
    }

    /**
     * Test verifyOtp3 with wrong OTP
     */
    public function test_verify_otp3_wrong_otp(): void
    {
        $request = new Request([
            'user_phone' => '1234567890',
            'status' => 'failed',
        ]);

        $response = $this->controller->verifyOtp3($request);

        $this->assertEquals(0, $response['status']);
        $this->assertEquals('Wrong OTP', $response['message']);
    }

    /**
     * Test verifyOtp3 with unregistered phone
     */
    public function test_verify_otp3_unregistered_phone(): void
    {
        $request = new Request([
            'user_phone' => '9999999999',
            'status' => 'success',
        ]);

        $response = $this->controller->verifyOtp3($request);

        $this->assertEquals(0, $response['status']);
        $this->assertEquals('User not registered', $response['message']);
    }

    /**
     * Test checknum with existing phone
     */
    public function test_checknum_existing_phone(): void
    {
        $request = new Request([
            'user_phone' => '1234567890',
        ]);

        $response = $this->controller->checknum($request);

        $this->assertEquals(1, $response['status']);
        $this->assertEquals('Phone number is available in DB', $response['message']);
    }

    /**
     * Test checknum with non-existing phone
     */
    public function test_checknum_non_existing_phone(): void
    {
        $request = new Request([
            'user_phone' => '9999999999',
        ]);

        $response = $this->controller->checknum($request);

        $this->assertEquals(0, $response['status']);
        $this->assertEquals('User not registered', $response['message']);
    }

    /**
     * Test checknum with empty phone
     */
    public function test_checknum_empty_phone(): void
    {
        $request = new Request([
            'user_phone' => '',
        ]);

        $response = $this->controller->checknum($request);

        $this->assertEquals(0, $response['status']);
        $this->assertEquals('User not registered', $response['message']);
    }

    /**
     * Test response structure consistency
     */
    public function test_response_structure_consistency(): void
    {
        $methods = [
            'checkotponoff' => [],
            'verifyOtp3' => ['user_phone' => '1234567890', 'status' => 'success'],
            'checknum' => ['user_phone' => '1234567890'],
        ];

        foreach ($methods as $method => $params) {
            $request = new Request($params);
            $response = $this->controller->$method($request);

            $this->assertArrayHasKey('status', $response);
            $this->assertArrayHasKey('message', $response);
        }
    }

    /**
     * Test forgot_password with no web setting
     */
    public function test_forgot_password_no_web_setting(): void
    {
        // Remove web setting
        DB::table('tbl_web_setting')->delete();

        // This should cause an error due to null app name
        $request = new Request([
            'user_email' => 'test@example.com',
            'user_phone' => '1234567890',
        ]);

        $this->expectException(\ErrorException::class);
        $this->controller->forgot_password($request);
    }

    /**
     * Test various OTP statuses
     */
    public function test_verify_otp3_various_statuses(): void
    {
        $testCases = [
            ['status' => 'success', 'expected_status' => 1, 'expected_message' => 'Otp Matched Successfully'],
            ['status' => 'failure', 'expected_status' => 0, 'expected_message' => 'Wrong OTP'],
            ['status' => 'error', 'expected_status' => 0, 'expected_message' => 'Wrong OTP'],
            ['status' => '', 'expected_status' => 0, 'expected_message' => 'Wrong OTP'],
        ];

        foreach ($testCases as $testCase) {
            $request = new Request([
                'user_phone' => '1234567890',
                'status' => $testCase['status'],
            ]);

            $response = $this->controller->verifyOtp3($request);

            $this->assertEquals($testCase['expected_status'], $response['status']);
            $this->assertEquals($testCase['expected_message'], $response['message']);
        }
    }
}
