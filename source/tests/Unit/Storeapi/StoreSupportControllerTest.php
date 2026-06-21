<?php

declare(strict_types=1);

namespace Tests\Unit\Storeapi;

use Tests\StoreApiTestCase;

class StoreSupportControllerTest extends StoreApiTestCase
{
    /**
     * Test submitting store feedback successfully
     */
    public function test_submit_store_feedback_success(): void
    {
        $response = $this->storeApiCall('POST', 'store_feedback', [
            'store_id' => $this->store->id,
            'message' => 'The app is working great! Love the new features.',
            'subject' => 'Positive Feedback',
        ]);

        $this->assertApiSuccess($response, 'Thank you for your feedback');

        // Verify feedback was stored
        $this->assertDatabaseHas('store_feedback', [
            'store_id' => $this->store->id,
            'message' => 'The app is working great! Love the new features.',
            'subject' => 'Positive Feedback',
        ]);
    }

    /**
     * Test submitting feedback with empty message
     */
    public function test_submit_feedback_empty_message(): void
    {
        $response = $this->storeApiCall('POST', 'store_feedback', [
            'store_id' => $this->store->id,
            'message' => '',
            'subject' => 'Test Subject',
        ]);

        $this->assertApiError($response, 'Message is required');
    }

    /**
     * Test submitting feedback with empty subject
     */
    public function test_submit_feedback_empty_subject(): void
    {
        $response = $this->storeApiCall('POST', 'store_feedback', [
            'store_id' => $this->store->id,
            'message' => 'Test message',
            'subject' => '',
        ]);

        $this->assertApiError($response, 'Subject is required');
    }

    /**
     * Test submitting feedback with invalid store ID
     */
    public function test_submit_feedback_invalid_store(): void
    {
        $response = $this->storeApiCall('POST', 'store_feedback', [
            'store_id' => 99999,
            'message' => 'Test message',
            'subject' => 'Test Subject',
        ]);

        $this->assertApiError($response, 'Store not found');
    }

    /**
     * Test submitting bug report
     */
    public function test_submit_bug_report(): void
    {
        $response = $this->storeApiCall('POST', 'store_feedback', [
            'store_id' => $this->store->id,
            'message' => 'The order confirmation page is not loading properly. It shows a blank screen after clicking confirm.',
            'subject' => 'Bug Report - Order Confirmation',
        ]);

        $this->assertApiSuccess($response, 'Thank you for your feedback');

        // Verify bug report was stored
        $this->assertDatabaseHas('store_feedback', [
            'store_id' => $this->store->id,
            'subject' => 'Bug Report - Order Confirmation',
        ]);
    }

    /**
     * Test submitting feature request
     */
    public function test_submit_feature_request(): void
    {
        $response = $this->storeApiCall('POST', 'store_feedback', [
            'store_id' => $this->store->id,
            'message' => 'It would be great to have a bulk product upload feature to add multiple products at once.',
            'subject' => 'Feature Request - Bulk Upload',
        ]);

        $this->assertApiSuccess($response, 'Thank you for your feedback');

        // Verify feature request was stored
        $this->assertDatabaseHas('store_feedback', [
            'store_id' => $this->store->id,
            'subject' => 'Feature Request - Bulk Upload',
        ]);
    }

    /**
     * Test submitting feedback with long message
     */
    public function test_submit_feedback_long_message(): void
    {
        $longMessage = str_repeat('This is a very long feedback message. ', 50);

        $response = $this->storeApiCall('POST', 'store_feedback', [
            'store_id' => $this->store->id,
            'message' => $longMessage,
            'subject' => 'Long Feedback',
        ]);

        $this->assertApiSuccess($response, 'Thank you for your feedback');

        // Verify long message was stored (assuming no length limit or appropriate limit)
        $this->assertDatabaseHas('store_feedback', [
            'store_id' => $this->store->id,
            'subject' => 'Long Feedback',
        ]);
    }

    /**
     * Test submitting multiple feedback from same store
     */
    public function test_submit_multiple_feedback(): void
    {
        $feedbacks = [
            ['message' => 'First feedback', 'subject' => 'First Subject'],
            ['message' => 'Second feedback', 'subject' => 'Second Subject'],
            ['message' => 'Third feedback', 'subject' => 'Third Subject'],
        ];

        foreach ($feedbacks as $feedback) {
            $response = $this->storeApiCall('POST', 'store_feedback', [
                'store_id' => $this->store->id,
                'message' => $feedback['message'],
                'subject' => $feedback['subject'],
            ]);

            $this->assertApiSuccess($response, 'Thank you for your feedback');
        }

        // Verify all feedback was stored
        foreach ($feedbacks as $feedback) {
            $this->assertDatabaseHas('store_feedback', [
                'store_id' => $this->store->id,
                'message' => $feedback['message'],
                'subject' => $feedback['subject'],
            ]);
        }

        // Verify total count
        $feedbackCount = \DB::table('store_feedback')
            ->where('store_id', $this->store->id)
            ->count();

        $this->assertEquals(3, $feedbackCount);
    }

    /**
     * Test feedback timestamp is recorded
     */
    public function test_feedback_timestamp_recorded(): void
    {
        $beforeSubmission = now()->subMinute();

        $response = $this->storeApiCall('POST', 'store_feedback', [
            'store_id' => $this->store->id,
            'message' => 'Timestamped feedback',
            'subject' => 'Timestamp Test',
        ]);

        $this->assertApiSuccess($response, 'Thank you for your feedback');

        $afterSubmission = now()->addMinute();

        // Verify feedback has timestamp within reasonable range
        $feedback = \DB::table('store_feedback')
            ->where('store_id', $this->store->id)
            ->where('subject', 'Timestamp Test')
            ->first();

        $this->assertNotNull($feedback);
        $this->assertNotNull($feedback->created_at ?? $feedback->feedback_date);

        // Verify timestamp is recent
        $feedbackTime = \Carbon\Carbon::parse($feedback->created_at ?? $feedback->feedback_date);
        $this->assertTrue($feedbackTime->between($beforeSubmission, $afterSubmission));
    }

    /**
     * Test feedback contains store information
     */
    public function test_feedback_contains_store_info(): void
    {
        $response = $this->storeApiCall('POST', 'store_feedback', [
            'store_id' => $this->store->id,
            'message' => 'Test feedback with store info',
            'subject' => 'Store Info Test',
        ]);

        $this->assertApiSuccess($response, 'Thank you for your feedback');

        // Verify feedback is linked to correct store
        $feedback = \DB::table('store_feedback')
            ->where('store_id', $this->store->id)
            ->where('subject', 'Store Info Test')
            ->first();

        $this->assertNotNull($feedback);
        $this->assertEquals($this->store->id, $feedback->store_id);
    }

    /**
     * Test HTML in feedback is handled properly
     */
    public function test_feedback_with_html_content(): void
    {
        $messageWithHtml = 'This is <script>alert("test")</script> a test message with <b>HTML</b> content.';

        $response = $this->storeApiCall('POST', 'store_feedback', [
            'store_id' => $this->store->id,
            'message' => $messageWithHtml,
            'subject' => 'HTML Test',
        ]);

        $this->assertApiSuccess($response, 'Thank you for your feedback');

        // Verify feedback was stored (HTML handling depends on implementation)
        $this->assertDatabaseHas('store_feedback', [
            'store_id' => $this->store->id,
            'subject' => 'HTML Test',
        ]);
    }
}
