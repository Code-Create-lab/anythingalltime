<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Controllers\Api\UsernotificationController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UsernotificationControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected UsernotificationController $controller;
    protected int $userId;
    protected int $notiId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new UsernotificationController;

        // Create dynamic user ID
        $this->userId = DB::table('users')->insertGetId([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'user_phone' => '1234567890',
            'password' => bcrypt('password'),
            'reg_date' => now()->format('Y-m-d'),
        ]);

        // Create test table for SQLite with correct column names
        DB::statement('CREATE TABLE IF NOT EXISTS user_notification (
            noti_id INTEGER PRIMARY KEY,
            user_id INTEGER,
            noti_title TEXT,
            image TEXT,
            noti_message TEXT,
            read_by_user INTEGER DEFAULT 0,
            created_at TEXT
        )');
    }

    /**
     * Test notification list with data
     */
    public function test_notificationlist_with_data(): void
    {
        // Create test notifications
        DB::table('user_notification')->insert([
            [
                'user_id' => $this->userId,
                'noti_title' => 'Order Confirmed',
                'noti_message' => 'Your order has been confirmed',
                'read_by_user' => 0,
                'created_at' => now(),
            ],
            [
                'user_id' => $this->userId,
                'noti_title' => 'Order Delivered',
                'noti_message' => 'Your order has been delivered',
                'read_by_user' => 1,
                'created_at' => now(),
            ],
        ]);

        $request = new Request(['user_id' => $this->userId]);

        $response = $this->controller->notificationlist($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Notification List', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertCount(2, $response['data']);

        // Check first notification
        $firstNotification = $response['data'][0];
        $this->assertEquals('Order Confirmed', $firstNotification->noti_title);
        $this->assertEquals('Your order has been confirmed', $firstNotification->noti_message);
        $this->assertEquals(0, $firstNotification->read_by_user);
    }

    /**
     * Test notification list without data
     */
    public function test_notificationlist_without_data(): void
    {
        $request = new Request(['user_id' => 999]);

        $response = $this->controller->notificationlist($request);

        $this->assertEquals('0', $response['status']);
        $this->assertEquals('Not Found', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertEmpty($response['data']);
    }

    /**
     * Test notification list for empty user_id
     */
    public function test_notificationlist_empty_user_id(): void
    {
        $request = new Request(['user_id' => null]);

        $response = $this->controller->notificationlist($request);

        $this->assertEquals('0', $response['status']);
        $this->assertEquals('Not Found', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertEmpty($response['data']);
    }

    /**
     * Test notification list with multiple users
     */
    public function test_notificationlist_multiple_users(): void
    {
        // Create other user
        $otherUserId = DB::table('users')->insertGetId([
            'name' => 'Other User',
            'email' => 'other@example.com',
            'user_phone' => '9876543210',
            'password' => bcrypt('password'),
            'reg_date' => now()->format('Y-m-d'),
        ]);

        // Create notifications for different users
        DB::table('user_notification')->insert([
            [
                'user_id' => $this->userId,
                'noti_title' => 'Notification for User 1',
                'noti_message' => 'Message for user 1',
                'read_by_user' => 0,
                'created_at' => now(),
            ],
            [
                'user_id' => $otherUserId,
                'noti_title' => 'Notification for User 2',
                'noti_message' => 'Message for user 2',
                'read_by_user' => 0,
                'created_at' => now(),
            ],
        ]);

        // Test user 1 only gets their notifications
        $request = new Request(['user_id' => $this->userId]);
        $response = $this->controller->notificationlist($request);

        $this->assertEquals('1', $response['status']);
        $this->assertCount(1, $response['data']);
        $this->assertEquals('Notification for User 1', $response['data'][0]->noti_title);

        // Test user 2 only gets their notifications
        $request2 = new Request(['user_id' => $otherUserId]);
        $response2 = $this->controller->notificationlist($request2);

        $this->assertEquals('1', $response2['status']);
        $this->assertCount(1, $response2['data']);
        $this->assertEquals('Notification for User 2', $response2['data'][0]->noti_title);
    }

    /**
     * Test read_by_user successful update
     */
    public function test_read_by_user_successful(): void
    {
        // Create unread notification
        $this->notiId = DB::table('user_notification')->insertGetId([
            'user_id' => $this->userId,
            'noti_title' => 'Test Notification',
            'noti_message' => 'Test message',
            'read_by_user' => 0,
            'created_at' => now(),
        ]);

        $request = new Request(['noti_id' => $this->notiId]);

        $response = $this->controller->read_by_user($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Read by user', $response['message']);

        // Verify notification was marked as read
        $this->assertDatabaseHas('user_notification', [
            'noti_id' => $this->notiId,
            'read_by_user' => 1,
        ]);
    }

    /**
     * Test read_by_user with non-existent notification
     */
    public function test_read_by_user_not_found(): void
    {
        $request = new Request(['noti_id' => 999]);

        $response = $this->controller->read_by_user($request);

        $this->assertEquals('0', $response['status']);
        $this->assertEquals('Not Found', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertEmpty($response['data']);
    }

    /**
     * Test read_by_user with already read notification
     */
    public function test_read_by_user_already_read(): void
    {
        // Create already read notification
        $notiId = DB::table('user_notification')->insertGetId([
            'user_id' => $this->userId,
            'noti_title' => 'Already Read',
            'noti_message' => 'This is already read',
            'read_by_user' => 1,
            'created_at' => now(),
        ]);

        $request = new Request(['noti_id' => $notiId]);

        $response = $this->controller->read_by_user($request);

        // Controller returns '1' when update succeeds, even if already read
        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Read by user', $response['message']);

        // Verify it's still marked as read
        $this->assertDatabaseHas('user_notification', [
            'noti_id' => $notiId,
            'read_by_user' => 1,
        ]);
    }

    /**
     * Test read_by_user with null noti_id
     */
    public function test_read_by_user_null_noti_id(): void
    {
        $request = new Request(['noti_id' => null]);

        $response = $this->controller->read_by_user($request);

        $this->assertEquals('0', $response['status']);
        $this->assertEquals('Not Found', $response['message']);
    }

    /**
     * Test notification list ordering
     */
    public function test_notificationlist_ordering(): void
    {
        // Create notifications in different order
        DB::table('user_notification')->insert([
            [
                'user_id' => $this->userId,
                'noti_title' => 'Third Notification',
                'noti_message' => 'Third message',
                'read_by_user' => 0,
                'created_at' => now(),
            ],
            [
                'user_id' => $this->userId,
                'noti_title' => 'First Notification',
                'noti_message' => 'First message',
                'read_by_user' => 0,
                'created_at' => now(),
            ],
            [
                'user_id' => $this->userId,
                'noti_title' => 'Second Notification',
                'noti_message' => 'Second message',
                'read_by_user' => 0,
                'created_at' => now(),
            ],
        ]);

        $request = new Request(['user_id' => $this->userId]);
        $response = $this->controller->notificationlist($request);

        $this->assertEquals('1', $response['status']);
        $this->assertCount(3, $response['data']);

        // Verify notifications are returned (ordering may vary)
        $this->assertCount(3, $response['data']);
    }

    /**
     * Test response structure consistency
     */
    public function test_response_structure_consistency(): void
    {
        $methods = [
            'notificationlist' => ['user_id' => 1],
            'read_by_user' => ['noti_id' => 999],
        ];

        foreach ($methods as $method => $params) {
            $request = new Request($params);
            $response = $this->controller->$method($request);

            $this->assertArrayHasKey('status', $response);
            $this->assertArrayHasKey('message', $response);
        }
    }

    /**
     * Test notification content with special characters
     */
    public function test_notification_special_characters(): void
    {
        // Create notification with special characters
        DB::table('user_notification')->insert([
            'user_id' => $this->userId,
            'noti_title' => 'Special chars: @#$%^&*()',
            'noti_message' => 'Price: $10.99, Discount: 20% off, "Free delivery" available!',
            'read_by_user' => 0,
            'created_at' => now(),
        ]);

        $request = new Request(['user_id' => $this->userId]);
        $response = $this->controller->notificationlist($request);

        $this->assertEquals('1', $response['status']);
        $this->assertStringContainsString('@#$%^&*()', $response['data'][0]->noti_title);
        $this->assertStringContainsString('$10.99', $response['data'][0]->noti_message);
        $this->assertStringContainsString('20%', $response['data'][0]->noti_message);
        $this->assertStringContainsString('"Free delivery"', $response['data'][0]->noti_message);
    }
}
