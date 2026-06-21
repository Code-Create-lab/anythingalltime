<?php

declare(strict_types=1);

namespace Tests\Unit\Driverapi;

use App\Models\DeliveryBoy;
use Tests\DriverApiTestCase;

class DriverLoginControllerTest extends DriverApiTestCase
{
    /**
     * Test successful driver login
     */
    public function test_driver_login_success(): void
    {
        $driver = DeliveryBoy::factory()->create([
            'boy_phone' => '9876543210',
            'password' => 'testpass123',
        ]);

        $response = $this->driverApiCall('POST', 'driver_login', [
            'phone' => '9876543210',
            'password' => 'testpass123',
            'device_id' => 'test-device-123',
        ]);

        $this->assertApiSuccess($response, 'login successfully');
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                0 => [
                    'dboy_id',
                    'boy_name',
                    'boy_phone',
                    'boy_city',
                    'device_id',
                    'lat',
                    'lng',
                ],
            ],
        ]);

        // Verify device ID was updated
        $this->assertDatabaseHas('delivery_boy', [
            'dboy_id' => $driver->dboy_id,
            'device_id' => 'test-device-123',
        ]);
    }

    /**
     * Test login with wrong password
     */
    public function test_driver_login_wrong_password(): void
    {
        DeliveryBoy::factory()->create([
            'boy_phone' => '9876543210',
            'password' => 'correctpass',
        ]);

        $response = $this->driverApiCall('POST', 'driver_login', [
            'phone' => '9876543210',
            'password' => 'wrongpass',
            'device_id' => 'test-device-123',
        ]);

        $this->assertApiError($response, 'Wrong Password');
    }

    /**
     * Test login with non-existent phone number
     */
    public function test_driver_login_not_registered(): void
    {
        $response = $this->driverApiCall('POST', 'driver_login', [
            'phone' => '1111111111',
            'password' => 'anypass',
            'device_id' => 'test-device-123',
        ]);

        $this->assertApiError($response, 'Driver Not Registered');
    }

    /**
     * Test driver profile retrieval
     */
    public function test_driver_profile_success(): void
    {
        $this->createDriverIncentive([
            'earned_till_now' => 500,
            'paid_till_now' => 300,
            'remaining' => 200,
        ]);

        $this->createDriverBankDetails([
            'ac_no' => '1234567890',
            'bank_name' => 'Test Bank',
        ]);

        $response = $this->driverApiCall('POST', 'driver_profile', [
            'dboy_id' => $this->driver->dboy_id,
        ]);

        $this->assertApiSuccess($response, 'Delivery Boy Profile');
        $response->assertJsonStructure([
            'status',
            'message',
            'total_incentive',
            'received_incentive',
            'remaining_incentive',
            'driver_data' => [
                'dboy_id',
                'boy_name',
                'boy_phone',
                'boy_city',
            ],
            'bank_details' => [
                'ac_no',
                'bank_name',
                'holder_name',
            ],
        ]);

        $response->assertJson([
            'total_incentive' => 500,
            'received_incentive' => 300,
            'remaining_incentive' => 200,
        ]);
    }

    /**
     * Test driver profile without incentive data
     */
    public function test_driver_profile_without_incentive(): void
    {
        $response = $this->driverApiCall('POST', 'driver_profile', [
            'dboy_id' => $this->driver->dboy_id,
        ]);

        $this->assertApiSuccess($response, 'Delivery Boy Profile');
        $response->assertJson([
            'total_incentive' => 0,
            'received_incentive' => 0,
            'remaining_incentive' => 0,
        ]);
    }

    /**
     * Test driver profile not found
     */
    public function test_driver_profile_not_found(): void
    {
        $response = $this->driverApiCall('POST', 'driver_profile', [
            'dboy_id' => 99999,
        ]);

        $this->assertApiError($response, 'Delivery Boy not found');
    }

    /**
     * Test updating driver bank details (new)
     */
    public function test_driver_bank_create_new(): void
    {
        $response = $this->driverApiCall('POST', 'driver_bank', [
            'dboy_id' => $this->driver->dboy_id,
            'ac_no' => '9876543210',
            'ifsc' => 'TEST0001234',
            'bank_name' => 'New Test Bank',
            'ac_holder' => 'Test Driver',
            'upi' => 'testdriver@upi',
        ]);

        $this->assertApiSuccess($response, 'Bank Details Updated Successfully');

        // Verify bank details were created
        $this->assertDatabaseHas('driver_bank', [
            'driver_id' => $this->driver->dboy_id,
            'ac_no' => '9876543210',
            'ifsc' => 'TEST0001234',
            'bank_name' => 'New Test Bank',
            'holder_name' => 'Test Driver',
            'upi' => 'testdriver@upi',
        ]);
    }

    /**
     * Test updating existing driver bank details
     */
    public function test_driver_bank_update_existing(): void
    {
        // Create existing bank details
        $this->createDriverBankDetails([
            'ac_no' => '1111111111',
            'bank_name' => 'Old Bank',
        ]);

        $response = $this->driverApiCall('POST', 'driver_bank', [
            'dboy_id' => $this->driver->dboy_id,
            'ac_no' => '2222222222',
            'ifsc' => 'UPDATED1234',
            'bank_name' => 'Updated Bank',
            'ac_holder' => 'Updated Name',
            'upi' => 'updated@upi',
        ]);

        $this->assertApiSuccess($response, 'Bank Details Updated Successfully');

        // Verify bank details were updated
        $this->assertDatabaseHas('driver_bank', [
            'driver_id' => $this->driver->dboy_id,
            'ac_no' => '2222222222',
            'ifsc' => 'UPDATED1234',
            'bank_name' => 'Updated Bank',
            'holder_name' => 'Updated Name',
            'upi' => 'updated@upi',
        ]);

        // Verify old details don't exist
        $this->assertDatabaseMissing('driver_bank', [
            'driver_id' => $this->driver->dboy_id,
            'ac_no' => '1111111111',
        ]);
    }

    /**
     * Test updating driver profile
     */
    public function test_driver_update_profile_success(): void
    {
        $response = $this->driverApiCall('POST', 'driverupdateprofile', [
            'dboy_id' => $this->driver->dboy_id,
            'boy_name' => 'Updated Driver Name',
            'boy_phone' => '1234567890',
            'password' => 'newpassword123',
        ]);

        $this->assertApiSuccess($response, 'Driver Profile Updated');
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'dboy_id',
                'boy_name',
                'boy_phone',
            ],
        ]);

        // Verify profile was updated
        $this->assertDatabaseHas('delivery_boy', [
            'dboy_id' => $this->driver->dboy_id,
            'boy_name' => 'Updated Driver Name',
            'boy_phone' => '1234567890',
            'password' => 'newpassword123',
        ]);

        // Test login with new credentials
        $loginResponse = $this->driverApiCall('POST', 'driver_login', [
            'phone' => '1234567890',
            'password' => 'newpassword123',
            'device_id' => 'test-device',
        ]);

        $this->assertApiSuccess($loginResponse, 'login successfully');
    }

    /**
     * Test updating driver profile with duplicate phone
     */
    public function test_driver_update_profile_duplicate_phone(): void
    {
        // Create another driver with a phone number
        DeliveryBoy::factory()->create([
            'boy_phone' => '5555555555',
        ]);

        $response = $this->driverApiCall('POST', 'driverupdateprofile', [
            'dboy_id' => $this->driver->dboy_id,
            'boy_name' => 'Updated Name',
            'boy_phone' => '5555555555', // Duplicate phone
            'password' => 'newpass',
        ]);

        $this->assertApiError($response, trans('keywords.This Phone Number Is Already Registered With Another Delivery Boy'));
    }

    /**
     * Test updating driver profile with no changes
     */
    public function test_driver_update_profile_no_changes(): void
    {
        $response = $this->driverApiCall('POST', 'driverupdateprofile', [
            'dboy_id' => $this->driver->dboy_id,
            'boy_name' => $this->driver->boy_name,
            'boy_phone' => $this->driver->boy_phone,
            'password' => $this->driver->password,
        ]);

        $response->assertJson([
            'status' => '0',
            'message' => 'Nothing to Update',
        ]);
    }
}
