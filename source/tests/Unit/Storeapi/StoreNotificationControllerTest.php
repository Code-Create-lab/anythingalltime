<?php

declare(strict_types=1);

namespace Tests\Unit\Storeapi;

use Tests\StoreApiTestCase;

class StoreNotificationControllerTest extends StoreApiTestCase
{
    /**
     * Test listing store notifications
     */
    public function test_list_store_notifications(): void
    {
        // Create notifications for this store
        \DB::table('store_notification')->insert([
            [
                'store_id' => $this->store->id,
                'notification_title' => 'New Order Received',
                'notification_msg' => 'You have received a new order #12345',
                'notification_date' => now()->format('Y-m-d H:i:s'),
                'seen' => 0,
            ],
            [
                'store_id' => $this->store->id,
                'notification_title' => 'Order Completed',
                'notification_msg' => 'Order #12344 has been completed',
                'notification_date' => now()->subHour()->format('Y-m-d H:i:s'),
                'seen' => 1,
            ],
        ]);

        $response = $this->storeApiCall('POST', 'st_notificationlist', [
            'store_id' => $this->store->id,
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
        $response = $this->storeApiCall('POST', 'st_notificationlist', [
            'store_id' => $this->store->id,
        ]);

        $this->assertApiError($response, 'No Notifications');
    }

    /**
     * Test marking notification as read
     */
    public function test_mark_notification_as_read(): void
    {
        // Create unread notification
        $notificationId = \DB::table('store_notification')->insertGetId([
            'store_id' => $this->store->id,
            'notification_title' => 'Test Notification',
            'notification_msg' => 'Test message',
            'notification_date' => now()->format('Y-m-d H:i:s'),
            'seen' => 0,
        ]);

        // Update the record to set notification_id and not_id to match the id (like our migration does)
        \DB::table('store_notification')
            ->where('id', $notificationId)
            ->update([
                'notification_id' => $notificationId,
                'not_id' => $notificationId,
            ]);

        $response = $this->storeApiCall('POST', 'read_by_store', [
            'notification_id' => $notificationId,
        ]);

        $this->assertApiSuccess($response, 'Notification read');

        // Verify notification was marked as read
        $this->assertDatabaseHas('store_notification', [
            'notification_id' => $notificationId,
            'seen' => 1,
        ]);
    }

    /**
     * Test marking non-existent notification as read
     */
    public function test_mark_nonexistent_notification_as_read(): void
    {
        $response = $this->storeApiCall('POST', 'read_by_store', [
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
        \DB::table('store_notification')->insert([
            [
                'store_id' => $this->store->id,
                'notification_title' => 'Notification 1',
                'notification_msg' => 'Message 1',
                'notification_date' => now()->format('Y-m-d H:i:s'),
                'seen' => 0,
            ],
            [
                'store_id' => $this->store->id,
                'notification_title' => 'Notification 2',
                'notification_msg' => 'Message 2',
                'notification_date' => now()->format('Y-m-d H:i:s'),
                'seen' => 0,
            ],
        ]);

        $response = $this->storeApiCall('POST', 'all_as_read', [
            'store_id' => $this->store->id,
        ]);

        $this->assertApiSuccess($response, 'All notifications marked as read');

        // Verify all notifications were marked as read
        $unreadCount = \DB::table('store_notification')
            ->where('store_id', $this->store->id)
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
        \DB::table('store_notification')->insert([
            [
                'store_id' => $this->store->id,
                'notification_title' => 'To Delete 1',
                'notification_msg' => 'Message 1',
                'notification_date' => now()->format('Y-m-d H:i:s'),
                'seen' => 0,
            ],
            [
                'store_id' => $this->store->id,
                'notification_title' => 'To Delete 2',
                'notification_msg' => 'Message 2',
                'notification_date' => now()->format('Y-m-d H:i:s'),
                'seen' => 1,
            ],
        ]);

        $response = $this->storeApiCall('POST', 'st_delete_all_notification', [
            'store_id' => $this->store->id,
        ]);

        $this->assertApiSuccess($response, 'All notifications deleted');

        // Verify all notifications were deleted
        $notificationCount = \DB::table('store_notification')
            ->where('store_id', $this->store->id)
            ->count();

        $this->assertEquals(0, $notificationCount);
    }

    /**
     * Test notifications ordering (newest first)
     */
    public function test_notifications_ordered_by_date(): void
    {
        // Create notifications with different dates
        \DB::table('store_notification')->insert([
            [
                'store_id' => $this->store->id,
                'notification_title' => 'Oldest',
                'notification_msg' => 'Oldest message',
                'notification_date' => now()->subHours(3)->format('Y-m-d H:i:s'),
                'seen' => 0,
            ],
            [
                'store_id' => $this->store->id,
                'notification_title' => 'Newest',
                'notification_msg' => 'Newest message',
                'notification_date' => now()->format('Y-m-d H:i:s'),
                'seen' => 0,
            ],
            [
                'store_id' => $this->store->id,
                'notification_title' => 'Middle',
                'notification_msg' => 'Middle message',
                'notification_date' => now()->subHour()->format('Y-m-d H:i:s'),
                'seen' => 0,
            ],
        ]);

        $response = $this->storeApiCall('POST', 'st_notificationlist', [
            'store_id' => $this->store->id,
        ]);

        $this->assertApiSuccess($response, 'Notification List');
        $data = $response->json('data');

        // Verify order (newest first)
        $this->assertEquals('Newest', $data[0]['notification_title']);
        $this->assertEquals('Middle', $data[1]['notification_title']);
        $this->assertEquals('Oldest', $data[2]['notification_title']);
    }

    /**
     * Test notification count limits
     */
    public function test_notification_list_with_limit(): void
    {
        // Create many notifications
        for ($i = 1; $i <= 25; $i++) {
            \DB::table('store_notification')->insert([
                'store_id' => $this->store->id,
                'notification_title' => "Notification $i",
                'notification_msg' => "Message $i",
                'notification_date' => now()->subMinutes($i)->format('Y-m-d H:i:s'),
                'seen' => 0,
            ]);
        }

        $response = $this->storeApiCall('POST', 'st_notificationlist', [
            'store_id' => $this->store->id,
        ]);

        $this->assertApiSuccess($response, 'Notification List');

        // Assuming there's a limit (typically 20)
        $data = $response->json('data');
        $this->assertLessThanOrEqual(20, count($data));
    }

    /**
     * Test notifications only for specific store
     */
    public function test_notifications_filtered_by_store(): void
    {
        // Create another store
        $otherStore = $this->createTestStore();

        // Create notifications for both stores
        \DB::table('store_notification')->insert([
            [
                'store_id' => $this->store->id,
                'notification_title' => 'My Store Notification',
                'notification_msg' => 'Message for my store',
                'notification_date' => now()->format('Y-m-d H:i:s'),
                'seen' => 0,
            ],
            [
                'store_id' => $otherStore->id,
                'notification_title' => 'Other Store Notification',
                'notification_msg' => 'Message for other store',
                'notification_date' => now()->format('Y-m-d H:i:s'),
                'seen' => 0,
            ],
        ]);

        $response = $this->storeApiCall('POST', 'st_notificationlist', [
            'store_id' => $this->store->id,
        ]);

        $this->assertApiSuccess($response, 'Notification List');
        $data = $response->json('data');

        $this->assertCount(1, $data);
        $this->assertEquals('My Store Notification', $data[0]['notification_title']);
    }
}
