<?php

declare(strict_types=1);

namespace Tests\Unit\Driverapi;

use Tests\DriverApiTestCase;

class DriverStatusControllerTest extends DriverApiTestCase
{
    /**
     * Test updating driver status to online
     */
    public function test_update_status_to_online(): void
    {
        // Start with offline status
        $this->driver->update(['status' => 0]);

        $response = $this->driverApiCall('POST', 'update_status', [
            'dboy_id' => $this->driver->dboy_id,
            'status' => 1, // Online
            'lat' => '12.9716',
            'lng' => '77.5946',
        ]);

        $this->assertApiSuccess($response, 'Status Updated');

        // Verify status and location were updated
        $this->assertDatabaseHas('delivery_boy', [
            'dboy_id' => $this->driver->dboy_id,
            'status' => 1,
            'lat' => '12.9716',
            'lng' => '77.5946',
        ]);
    }

    /**
     * Test updating driver status to offline
     */
    public function test_update_status_to_offline(): void
    {
        // Start with online status
        $this->driver->update(['status' => 1]);

        $response = $this->driverApiCall('POST', 'update_status', [
            'dboy_id' => $this->driver->dboy_id,
            'status' => 0, // Offline
            'lat' => '12.9716',
            'lng' => '77.5946',
        ]);

        $this->assertApiSuccess($response, 'Status Updated');

        // Verify status was updated
        $this->assertDatabaseHas('delivery_boy', [
            'dboy_id' => $this->driver->dboy_id,
            'status' => 0,
        ]);
    }

    /**
     * Test updating driver status with location update
     */
    public function test_update_status_with_location(): void
    {
        $newLat = '13.0827';
        $newLng = '80.2707';

        $response = $this->driverApiCall('POST', 'update_status', [
            'dboy_id' => $this->driver->dboy_id,
            'status' => 1,
            'lat' => $newLat,
            'lng' => $newLng,
        ]);

        $this->assertApiSuccess($response, 'Status Updated');

        // Verify location was updated
        $this->assertDatabaseHas('delivery_boy', [
            'dboy_id' => $this->driver->dboy_id,
            'lat' => $newLat,
            'lng' => $newLng,
        ]);
    }

    /**
     * Test getting current driver status
     */
    public function test_get_driver_status(): void
    {
        // Set known status and location
        $this->driver->update([
            'status' => 1,
            'lat' => '12.9716',
            'lng' => '77.5946',
        ]);

        $response = $this->driverApiCall('POST', 'driver_status', [
            'dboy_id' => $this->driver->dboy_id,
        ]);

        $this->assertApiSuccess($response, 'Driver Status');
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'dboy_id',
                'boy_name',
                'status',
                'lat',
                'lng',
            ],
        ]);

        $response->assertJsonFragment([
            'status' => '1',
            'data' => [
                'dboy_id' => $this->driver->dboy_id,
                'boy_name' => $this->driver->boy_name,
                'status' => 1,
                'lat' => '12.9716',
                'lng' => '77.5946',
            ],
        ]);
    }

    /**
     * Test getting status for non-existent driver
     */
    public function test_get_status_driver_not_found(): void
    {
        $response = $this->driverApiCall('POST', 'driver_status', [
            'dboy_id' => 99999,
        ]);

        $this->assertApiError($response, 'Driver not found');
    }

    /**
     * Test status update with invalid status value
     */
    public function test_update_status_invalid_value(): void
    {
        $response = $this->driverApiCall('POST', 'update_status', [
            'dboy_id' => $this->driver->dboy_id,
            'status' => 5, // Invalid status
            'lat' => '12.9716',
            'lng' => '77.5946',
        ]);

        // Should still work but only accept 0 or 1
        $this->assertApiSuccess($response, 'Status Updated');

        // Verify status is normalized (typically would be set to 1 for any truthy value)
        $this->assertDatabaseHas('delivery_boy', [
            'dboy_id' => $this->driver->dboy_id,
            'status' => 1, // Assuming invalid values default to online
        ]);
    }

    /**
     * Test status update for non-existent driver
     */
    public function test_update_status_driver_not_found(): void
    {
        $response = $this->driverApiCall('POST', 'update_status', [
            'dboy_id' => 99999,
            'status' => 1,
            'lat' => '12.9716',
            'lng' => '77.5946',
        ]);

        $this->assertApiError($response, 'Driver not found');
    }

    /**
     * Test status update without location coordinates
     */
    public function test_update_status_without_coordinates(): void
    {
        // Ensure driver record is properly initialized
        $this->driver->update(['status' => 0]);

        $response = $this->driverApiCall('POST', 'update_status', [
            'dboy_id' => $this->driver->dboy_id,
            'status' => 1,
        ]);

        $this->assertApiSuccess($response, 'Status Updated');

        // Verify status was updated even without coordinates
        $this->assertDatabaseHas('delivery_boy', [
            'dboy_id' => $this->driver->dboy_id,
            'status' => 1,
        ]);
    }

    /**
     * Test continuous location updates while online
     */
    public function test_continuous_location_updates(): void
    {
        // Set driver online
        $this->driver->update(['status' => 1]);

        $locations = [
            ['lat' => '12.9716', 'lng' => '77.5946'],
            ['lat' => '12.9800', 'lng' => '77.6000'],
            ['lat' => '12.9900', 'lng' => '77.6100'],
        ];

        foreach ($locations as $location) {
            $response = $this->driverApiCall('POST', 'update_status', [
                'dboy_id' => $this->driver->dboy_id,
                'status' => 1,
                'lat' => $location['lat'],
                'lng' => $location['lng'],
            ]);

            $this->assertApiSuccess($response, 'Status Updated');

            // Verify location was updated
            $this->assertDatabaseHas('delivery_boy', [
                'dboy_id' => $this->driver->dboy_id,
                'lat' => $location['lat'],
                'lng' => $location['lng'],
            ]);
        }
    }

    /**
     * Test status transitions
     */
    public function test_status_transitions(): void
    {
        // Start offline
        $this->driver->update(['status' => 0]);

        // Go online
        $response = $this->driverApiCall('POST', 'update_status', [
            'dboy_id' => $this->driver->dboy_id,
            'status' => 1,
            'lat' => '12.9716',
            'lng' => '77.5946',
        ]);
        $this->assertApiSuccess($response, 'Status Updated');

        // Verify online status
        $statusResponse = $this->driverApiCall('POST', 'driver_status', [
            'dboy_id' => $this->driver->dboy_id,
        ]);
        $statusResponse->assertJsonFragment(['status' => 1]);

        // Go offline
        $response = $this->driverApiCall('POST', 'update_status', [
            'dboy_id' => $this->driver->dboy_id,
            'status' => 0,
        ]);
        $this->assertApiSuccess($response, 'Status Updated');

        // Verify offline status
        $statusResponse = $this->driverApiCall('POST', 'driver_status', [
            'dboy_id' => $this->driver->dboy_id,
        ]);
        $statusResponse->assertJsonFragment(['status' => 0]);
    }
}
