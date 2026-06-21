<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Controllers\Api\AppsetController;
use App\Models\NotificationBy;
use App\Models\User;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Tests\TestCase;

class AppsetControllerTest extends TestCase
{
    use WithFaker;

    protected AppsetController $controller;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new AppsetController;
        $this->user = User::factory()->create([
            'user_phone' => '1234567890',
        ]);
    }

    /**
     * Test getting user app notification settings (found)
     */
    public function test_appsetting_found(): void
    {
        NotificationBy::factory()->create([
            'user_id' => 1,
            'sms' => 1,
            'email' => 1,
            'app' => 1,
        ]);

        $request = new Request(['user_id' => 1]);
        $response = $this->controller->appsetting($request);
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('1', $responseData['status']);
        $this->assertEquals('User app notify settings', $responseData['message']);
        $this->assertArrayHasKey('data', $responseData);
    }

    /**
     * Test getting user app notification settings (not found)
     */
    public function test_appsetting_not_found(): void
    {
        $request = new Request(['user_id' => 1]);
        $response = $this->controller->appsetting($request);
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('0', $responseData['status']);
        $this->assertEquals('User settings Not Found', $responseData['message']);
    }

    /**
     * Test updating user app notification settings (success)
     */
    public function test_updateapp_success(): void
    {
        NotificationBy::factory()->create([
            'user_id' => 1,
            'sms' => 0,
            'email' => 0,
            'app' => 0,
        ]);

        $request = new Request([
            'user_id' => 1,
            'sms' => 1,
            'email' => 1,
            'app' => 1,
        ]);
        $response = $this->controller->updateapp($request);
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('1', $responseData['status']);
        $this->assertEquals('Updated Successfully', $responseData['message']);
        $this->assertDatabaseHas('notificationby', [
            'user_id' => 1,
            'sms' => 1,
            'email' => 1,
            'app' => 1,
        ]);
    }

    /**
     * Test updating user app notification settings (already updated)
     */
    public function test_updateapp_already_updated(): void
    {
        // No record exists, so update returns 0
        $request = new Request([
            'user_id' => 1,
            'sms' => 1,
            'email' => 1,
            'app' => 1,
        ]);
        $response = $this->controller->updateapp($request);
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('0', $responseData['status']);
        $this->assertEquals('Already Updated', $responseData['message']);
    }
}
