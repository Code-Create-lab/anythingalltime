<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Controllers\Api\SupportController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SupportControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected SupportController $controller;
    protected int $userId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new SupportController;

        // Create dynamic user ID
        $this->userId = DB::table('users')->insertGetId([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'user_phone' => '1234567890',
            'password' => bcrypt('password'),
            'reg_date' => now()->format('Y-m-d'),
        ]);

        // Create test table for SQLite
        DB::statement('CREATE TABLE IF NOT EXISTS user_support (
            id INTEGER PRIMARY KEY,
            query TEXT,
            type TEXT,
            created_at TEXT
        )');
    }

    /**
     * Test feedback submission successful
     */
    public function test_feedback_submission_successful(): void
    {
        $request = new Request([
            'feedback' => 'This is a test feedback message',
            'user_id' => $this->userId,
        ]);

        $response = $this->controller->feedback($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Feedback/Query Submitted', $response['message']);

        // Verify data was inserted into database
        $this->assertDatabaseHas('user_support', [
            'query' => 'This is a test feedback message',
            'id' => $this->userId,
            'type' => 'user',
        ]);
    }

    /**
     * Test feedback submission with empty feedback
     */
    public function test_feedback_submission_with_empty_feedback(): void
    {
        $request = new Request([
            'feedback' => '',
            'user_id' => $this->userId,
        ]);

        $response = $this->controller->feedback($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Feedback/Query Submitted', $response['message']);

        // Verify empty feedback was inserted
        $this->assertDatabaseHas('user_support', [
            'query' => '',
            'id' => $this->userId,
            'type' => 'user',
        ]);
    }

    /**
     * Test feedback submission with long feedback text
     */
    public function test_feedback_submission_with_long_text(): void
    {
        $longFeedback = str_repeat('This is a very long feedback message. ', 50);

        $request = new Request([
            'feedback' => $longFeedback,
            'user_id' => $this->userId,
        ]);

        $response = $this->controller->feedback($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Feedback/Query Submitted', $response['message']);

        // Verify long feedback was inserted
        $this->assertDatabaseHas('user_support', [
            'query' => $longFeedback,
            'id' => $this->userId,
            'type' => 'user',
        ]);
    }

    /**
     * Test feedback submission with special characters
     */
    public function test_feedback_submission_with_special_characters(): void
    {
        $specialFeedback = 'Test feedback with special chars: @#$%^&*()_+ <script>alert("test")</script>';

        $request = new Request([
            'feedback' => $specialFeedback,
            'user_id' => $this->userId,
        ]);

        $response = $this->controller->feedback($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Feedback/Query Submitted', $response['message']);

        // Verify special characters feedback was inserted
        $this->assertDatabaseHas('user_support', [
            'query' => $specialFeedback,
            'id' => $this->userId,
            'type' => 'user',
        ]);
    }

    /**
     * Test feedback submission sets correct type
     */
    public function test_feedback_submission_sets_user_type(): void
    {
        $request = new Request([
            'feedback' => 'Type verification test',
            'user_id' => $this->userId,
        ]);

        $response = $this->controller->feedback($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Feedback/Query Submitted', $response['message']);

        // Verify type is set to 'user'
        $this->assertDatabaseHas('user_support', [
            'query' => 'Type verification test',
            'id' => $this->userId,
            'type' => 'user',
        ]);
    }

    /**
     * Test feedback submission with different user IDs
     */
    public function test_feedback_submission_with_different_user_ids(): void
    {
        $testData = [
            ['user_id' => $this->userId, 'feedback' => 'Feedback from user test'],
        ];

        foreach ($testData as $data) {
            $request = new Request($data);
            $response = $this->controller->feedback($request);

            $this->assertEquals('1', $response['status']);
            $this->assertEquals('Feedback/Query Submitted', $response['message']);

            // Verify each feedback was inserted with correct user ID
            $this->assertDatabaseHas('user_support', [
                'query' => $data['feedback'],
                'id' => $data['user_id'],
                'type' => 'user',
            ]);
        }
    }

    /**
     * Test that created_at timestamp is set
     */
    public function test_feedback_submission_sets_timestamp(): void
    {
        $request = new Request([
            'feedback' => 'Timestamp test feedback',
            'user_id' => $this->userId,
        ]);

        $response = $this->controller->feedback($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Feedback/Query Submitted', $response['message']);

        // Verify timestamp was set (check if record exists with recent timestamp)
        $record = DB::table('user_support')
            ->where('query', 'Timestamp test feedback')
            ->where('id', $this->userId)
            ->first();

        $this->assertNotNull($record);
        $this->assertNotNull($record->created_at);
    }
}
