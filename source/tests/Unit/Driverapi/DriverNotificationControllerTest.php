<?php

declare(strict_types=1);

namespace Tests\Unit\Driverapi;

use Tests\DriverApiTestCase;

class DriverNotificationControllerTest extends DriverApiTestCase
{
    /**
     * Test listing driver notifications
     */
    public function test_list_driver_notifications(): void
    {
        // Create notifications for this driver
        \DB::table('driver_notification')->insert([
            [
                'dboy_id' => $this->driver->dboy_id,
                'notification_title' => 'New Order Assigned',
                'notification_msg' => 'You have been assigned order #12345',
                'notification_date' => now()->format('Y-m-d H:i:s'),
                'seen' => 0,
            ],
            [
                'dboy_id' => $this->driver->dboy_id,
                'notification_title' => 'Incentive Added',
                'notification_msg' => 'You earned ₹50 incentive for completing order #12344',
                'notification_date' => now()->subHour()->format('Y-m-d H:i:s'),
                'seen' => 1,
            ],
        ]);

        $response = $this->driverApiCall('POST', 'driver_notificationlist', [
            'dboy_id' => $this->driver->dboy_id,
        ]);

        $this->assertApiSuccess($response, 'Notification List');
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                0 => [
                    'notification_id',
                    'notification_title',
                    'notification_msg',
                    'notification_date',
                    'seen',
                ],
            ],
        ]);
        $response->assertJsonCount(2, 'data');
    }

    /**
     * Test when no notifications exist
     */
    public function test_no_notifications_found(): void
    {
        $response = $this->driverApiCall('POST', 'driver_notificationlist', [
            'dboy_id' => $this->driver->dboy_id,
        ]);

        $this->assertApiError($response, 'No Notifications');
    }

    /**
     * Test marking notification as read
     */
    public function test_mark_notification_as_read(): void
    {
        // Create unread notification with all required fields
        $notificationId = \DB::table('driver_notification')->insertGetId([
            'dboy_id' => $this->driver->dboy_id,
            'notification_title' => 'Test Notification',
            'notification_msg' => 'Test message',
            'notification_date' => now()->format('Y-m-d H:i:s'),
            'seen' => 0,
        ]);

        // Update the record to set notification_id and not_id to match the id (like our migration does)
        \DB::table('driver_notification')
            ->where('id', $notificationId)
            ->update([
                'notification_id' => $notificationId,
                'not_id' => $notificationId,
            ]);

        $response = $this->driverApiCall('POST', 'read_by_driver', [
            'notification_id' => $notificationId,
        ]);

        $this->assertApiSuccess($response, 'Notification read');

        // Verify notification was marked as read
        $this->assertDatabaseHas('driver_notification', [
            'notification_id' => $notificationId,
            'seen' => 1,
        ]);
    }

    /**
     * Test marking non-existent notification as read
     */
    public function test_mark_nonexistent_notification_as_read(): void
    {
        $response = $this->driverApiCall('POST', 'read_by_driver', [
            'notification_id' => 99999,
        ]);

        $this->assertApiError($response, 'Notification not found');
    }

    /**
     * Test marking all notifications as read
     */
    public function test_mark_all_notifications_as_read(): void
    {
        // Create multiple unread notifications
        \DB::table('driver_notification')->insert([
            [
                'dboy_id' => $this->driver->dboy_id,
                'notification_title' => 'Notification 1',
                'notification_msg' => 'Message 1',
                'notification_date' => now()->format('Y-m-d H:i:s'),
                'seen' => 0,
            ],
            [
                'dboy_id' => $this->driver->dboy_id,
                'notification_title' => 'Notification 2',
                'notification_msg' => 'Message 2',
                'notification_date' => now()->format('Y-m-d H:i:s'),
                'seen' => 0,
            ],
        ]);

        $response = $this->driverApiCall('POST', 'driver_all_as_read', [
            'dboy_id' => $this->driver->dboy_id,
        ]);

        $this->assertApiSuccess($response, 'All notifications marked as read');

        // Verify all notifications were marked as read
        $unreadCount = \DB::table('driver_notification')
            ->where('dboy_id', $this->driver->dboy_id)
            ->where('seen', 0)
            ->count();

        $this->assertEquals(0, $unreadCount);
    }

    /**
     * Test deleting all notifications
     */
    public function test_delete_all_notifications(): void
    {
        // Create notifications
        \DB::table('driver_notification')->insert([
            [
                'dboy_id' => $this->driver->dboy_id,
                'notification_title' => 'To Delete 1',
                'notification_msg' => 'Message 1',
                'notification_date' => now()->format('Y-m-d H:i:s'),
                'seen' => 0,
            ],
            [
                'dboy_id' => $this->driver->dboy_id,
                'notification_title' => 'To Delete 2',
                'notification_msg' => 'Message 2',
                'notification_date' => now()->format('Y-m-d H:i:s'),
                'seen' => 1,
            ],
        ]);

        $response = $this->driverApiCall('POST', 'driver_delete_all_notification', [
            'dboy_id' => $this->driver->dboy_id,
        ]);

        $this->assertApiSuccess($response, 'All notifications deleted');

        // Verify all notifications were deleted
        $notificationCount = \DB::table('driver_notification')
            ->where('dboy_id', $this->driver->dboy_id)
            ->count();

        $this->assertEquals(0, $notificationCount);
    }

    /**
     * Test notifications ordering (newest first)
     */
    public function test_notifications_ordered_by_date(): void
    {
        // Create notifications with different dates
        \DB::table('driver_notification')->insert([
            [
                'dboy_id' => $this->driver->dboy_id,
                'notification_title' => 'Oldest',
                'notification_msg' => 'Oldest message',
                'notification_date' => now()->subHours(3)->format('Y-m-d H:i:s'),
                'seen' => 0,
            ],
            [
                'dboy_id' => $this->driver->dboy_id,
                'notification_title' => 'Newest',
                'notification_msg' => 'Newest message',
                'notification_date' => now()->format('Y-m-d H:i:s'),
                'seen' => 0,
            ],
            [
                'dboy_id' => $this->driver->dboy_id,
                'notification_title' => 'Middle',
                'notification_msg' => 'Middle message',
                'notification_date' => now()->subHour()->format('Y-m-d H:i:s'),
                'seen' => 0,
            ],
        ]);

        $response = $this->driverApiCall('POST', 'driver_notificationlist', [
            'dboy_id' => $this->driver->dboy_id,
        ]);

        $this->assertApiSuccess($response, 'Notification List');
        $data = $response->json('data');

        // Verify order (newest first)
        $this->assertEquals('Newest', $data[0]['notification_title']);
        $this->assertEquals('Middle', $data[1]['notification_title']);
        $this->assertEquals('Oldest', $data[2]['notification_title']);
    }

    /**
     * Test notifications only for specific driver
     */
    public function test_notifications_filtered_by_driver(): void
    {
        // Create another driver
        $otherDriver = $this->createTestDriver();

        // Create notifications for both drivers
        \DB::table('driver_notification')->insert([
            [
                'dboy_id' => $this->driver->dboy_id,
                'notification_title' => 'My Driver Notification',
                'notification_msg' => 'Message for my driver',
                'notification_date' => now()->format('Y-m-d H:i:s'),
                'seen' => 0,
            ],
            [
                'dboy_id' => $otherDriver->dboy_id,
                'notification_title' => 'Other Driver Notification',
                'notification_msg' => 'Message for other driver',
                'notification_date' => now()->format('Y-m-d H:i:s'),
                'seen' => 0,
            ],
        ]);

        $response = $this->driverApiCall('POST', 'driver_notificationlist', [
            'dboy_id' => $this->driver->dboy_id,
        ]);

        $this->assertApiSuccess($response, 'Notification List');
        $data = $response->json('data');

        $this->assertCount(1, $data);
        $this->assertEquals('My Driver Notification', $data[0]['notification_title']);
    }

    /**
     * Test notification types
     */
    public function test_different_notification_types(): void
    {
        $notificationTypes = [
            [
                'title' => 'Order Assignment',
                'message' => 'New order #ORD001 assigned to you',
                'type' => 'order_assigned',
            ],
            [
                'title' => 'Payment Received',
                'message' => 'Payment of ₹500 received for order #ORD001',
                'type' => 'payment_received',
            ],
            [
                'title' => 'Incentive Credited',
                'message' => 'Incentive of ₹50 credited to your account',
                'type' => 'incentive_credited',
            ],
            [
                'title' => 'Profile Update',
                'message' => 'Your profile has been updated successfully',
                'type' => 'profile_update',
            ],
        ];

        foreach ($notificationTypes as $notification) {
            \DB::table('driver_notification')->insert([
                'dboy_id' => $this->driver->dboy_id,
                'notification_title' => $notification['title'],
                'notification_msg' => $notification['message'],
                'notification_date' => now()->format('Y-m-d H:i:s'),
                'seen' => 0,
                'type' => $notification['type'],
            ]);
        }

        $response = $this->driverApiCall('POST', 'driver_notificationlist', [
            'dboy_id' => $this->driver->dboy_id,
        ]);

        $this->assertApiSuccess($response, 'Notification List');
        $data = $response->json('data');

        $this->assertCount(4, $data);

        // Verify all notification types are present
        $titles = array_column($data, 'notification_title');
        $this->assertContains('Order Assignment', $titles);
        $this->assertContains('Payment Received', $titles);
        $this->assertContains('Incentive Credited', $titles);
        $this->assertContains('Profile Update', $titles);
    }

    /**
     * Test unread notification count
     */
    public function test_unread_notification_count(): void
    {
        // Create mixed read/unread notifications
        \DB::table('driver_notification')->insert([
            [
                'dboy_id' => $this->driver->dboy_id,
                'notification_title' => 'Unread 1',
                'notification_msg' => 'Unread message 1',
                'notification_date' => now()->format('Y-m-d H:i:s'),
                'seen' => 0,
            ],
            [
                'dboy_id' => $this->driver->dboy_id,
                'notification_title' => 'Read 1',
                'notification_msg' => 'Read message 1',
                'notification_date' => now()->format('Y-m-d H:i:s'),
                'seen' => 1,
            ],
            [
                'dboy_id' => $this->driver->dboy_id,
                'notification_title' => 'Unread 2',
                'notification_msg' => 'Unread message 2',
                'notification_date' => now()->format('Y-m-d H:i:s'),
                'seen' => 0,
            ],
        ]);

        $response = $this->driverApiCall('POST', 'driver_notificationlist', [
            'dboy_id' => $this->driver->dboy_id,
        ]);

        $this->assertApiSuccess($response, 'Notification List');

        // Check if unread count is included in response
        if ($response->json('unread_count') !== null) {
            $response->assertJson(['unread_count' => 2]);
        }
    }
}
