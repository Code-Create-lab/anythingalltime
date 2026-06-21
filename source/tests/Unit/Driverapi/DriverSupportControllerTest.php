<?php

declare(strict_types=1);

namespace Tests\Unit\Driverapi;

use Tests\DriverApiTestCase;

class DriverSupportControllerTest extends DriverApiTestCase
{
    /**
     * Test submitting driver feedback successfully
     */
    public function test_submit_driver_feedback_success(): void
    {
        $response = $this->driverApiCall('POST', 'driver_feedback', [
            'dboy_id' => $this->driver->dboy_id,
            'message' => 'The driver app is very user-friendly. Navigation is smooth and order details are clear.',
            'subject' => 'App Experience Feedback',
        ]);

        $this->assertApiSuccess($response, 'Thank you for your feedback');

        // Verify feedback was stored
        $this->assertDatabaseHas('driver_feedback', [
            'dboy_id' => $this->driver->dboy_id,
            'message' => 'The driver app is very user-friendly. Navigation is smooth and order details are clear.',
            'subject' => 'App Experience Feedback',
        ]);
    }

    /**
     * Test submitting feedback with empty message
     */
    public function test_submit_feedback_empty_message(): void
    {
        $response = $this->driverApiCall('POST', 'driver_feedback', [
            'dboy_id' => $this->driver->dboy_id,
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
        $response = $this->driverApiCall('POST', 'driver_feedback', [
            'dboy_id' => $this->driver->dboy_id,
            'message' => 'Test message',
            'subject' => '',
        ]);

        $this->assertApiError($response, 'Subject is required');
    }

    /**
     * Test submitting feedback with invalid driver ID
     */
    public function test_submit_feedback_invalid_driver(): void
    {
        $response = $this->driverApiCall('POST', 'driver_feedback', [
            'dboy_id' => 99999,
            'message' => 'Test message',
            'subject' => 'Test Subject',
        ]);

        $this->assertApiError($response, 'Driver not found');
    }

    /**
     * Test submitting delivery issue report
     */
    public function test_submit_delivery_issue_report(): void
    {
        $response = $this->driverApiCall('POST', 'driver_feedback', [
            'dboy_id' => $this->driver->dboy_id,
            'message' => 'Customer was not available at the delivery address. Called multiple times but no response. Had to return the order.',
            'subject' => 'Delivery Issue - Customer Unavailable',
        ]);

        $this->assertApiSuccess($response, 'Thank you for your feedback');

        // Verify delivery issue was stored
        $this->assertDatabaseHas('driver_feedback', [
            'dboy_id' => $this->driver->dboy_id,
            'subject' => 'Delivery Issue - Customer Unavailable',
        ]);
    }

    /**
     * Test submitting payment issue report
     */
    public function test_submit_payment_issue_report(): void
    {
        $response = $this->driverApiCall('POST', 'driver_feedback', [
            'dboy_id' => $this->driver->dboy_id,
            'message' => 'Customer paid ₹500 for COD order but the order amount was ₹550. There is a mismatch in the payment.',
            'subject' => 'Payment Issue - Amount Mismatch',
        ]);

        $this->assertApiSuccess($response, 'Thank you for your feedback');

        // Verify payment issue was stored
        $this->assertDatabaseHas('driver_feedback', [
            'dboy_id' => $this->driver->dboy_id,
            'subject' => 'Payment Issue - Amount Mismatch',
        ]);
    }

    /**
     * Test submitting app bug report
     */
    public function test_submit_app_bug_report(): void
    {
        $response = $this->driverApiCall('POST', 'driver_feedback', [
            'dboy_id' => $this->driver->dboy_id,
            'message' => 'The app crashes when trying to mark an order as delivered. This happens consistently on Android 12.',
            'subject' => 'Bug Report - App Crash on Delivery',
        ]);

        $this->assertApiSuccess($response, 'Thank you for your feedback');

        // Verify bug report was stored
        $this->assertDatabaseHas('driver_feedback', [
            'dboy_id' => $this->driver->dboy_id,
            'subject' => 'Bug Report - App Crash on Delivery',
        ]);
    }

    /**
     * Test submitting feature request
     */
    public function test_submit_feature_request(): void
    {
        $response = $this->driverApiCall('POST', 'driver_feedback', [
            'dboy_id' => $this->driver->dboy_id,
            'message' => 'It would be helpful to have a route optimization feature that suggests the best route for multiple deliveries.',
            'subject' => 'Feature Request - Route Optimization',
        ]);

        $this->assertApiSuccess($response, 'Thank you for your feedback');

        // Verify feature request was stored
        $this->assertDatabaseHas('driver_feedback', [
            'dboy_id' => $this->driver->dboy_id,
            'subject' => 'Feature Request - Route Optimization',
        ]);
    }

    /**
     * Test submitting incentive inquiry
     */
    public function test_submit_incentive_inquiry(): void
    {
        $response = $this->driverApiCall('POST', 'driver_feedback', [
            'dboy_id' => $this->driver->dboy_id,
            'message' => 'I completed 50 deliveries last week but the incentive amount seems incorrect. Please verify my incentive calculation.',
            'subject' => 'Incentive Inquiry',
        ]);

        $this->assertApiSuccess($response, 'Thank you for your feedback');

        // Verify incentive inquiry was stored
        $this->assertDatabaseHas('driver_feedback', [
            'dboy_id' => $this->driver->dboy_id,
            'subject' => 'Incentive Inquiry',
        ]);
    }

    /**
     * Test submitting feedback with long message
     */
    public function test_submit_feedback_long_message(): void
    {
        $longMessage = str_repeat('This is a detailed feedback about the driver experience. ', 30);

        $response = $this->driverApiCall('POST', 'driver_feedback', [
            'dboy_id' => $this->driver->dboy_id,
            'message' => $longMessage,
            'subject' => 'Detailed Feedback',
        ]);

        $this->assertApiSuccess($response, 'Thank you for your feedback');

        // Verify long message was stored
        $this->assertDatabaseHas('driver_feedback', [
            'dboy_id' => $this->driver->dboy_id,
            'subject' => 'Detailed Feedback',
        ]);
    }

    /**
     * Test submitting multiple feedback from same driver
     */
    public function test_submit_multiple_feedback(): void
    {
        $feedbacks = [
            ['message' => 'Great app performance today', 'subject' => 'Performance Feedback'],
            ['message' => 'Customer was very satisfied', 'subject' => 'Customer Satisfaction'],
            ['message' => 'Need better GPS accuracy', 'subject' => 'GPS Issue'],
        ];

        foreach ($feedbacks as $feedback) {
            $response = $this->driverApiCall('POST', 'driver_feedback', [
                'dboy_id' => $this->driver->dboy_id,
                'message' => $feedback['message'],
                'subject' => $feedback['subject'],
            ]);

            $this->assertApiSuccess($response, 'Thank you for your feedback');
        }

        // Verify all feedback was stored
        foreach ($feedbacks as $feedback) {
            $this->assertDatabaseHas('driver_feedback', [
                'dboy_id' => $this->driver->dboy_id,
                'message' => $feedback['message'],
                'subject' => $feedback['subject'],
            ]);
        }

        // Verify total count
        $feedbackCount = \DB::table('driver_feedback')
            ->where('dboy_id', $this->driver->dboy_id)
            ->count();

        $this->assertEquals(3, $feedbackCount);
    }

    /**
     * Test feedback timestamp is recorded
     */
    public function test_feedback_timestamp_recorded(): void
    {
        $beforeSubmission = now()->subMinute();

        $response = $this->driverApiCall('POST', 'driver_feedback', [
            'dboy_id' => $this->driver->dboy_id,
            'message' => 'Timestamped feedback',
            'subject' => 'Timestamp Test',
        ]);

        $this->assertApiSuccess($response, 'Thank you for your feedback');

        $afterSubmission = now()->addMinute();

        // Verify feedback has timestamp within reasonable range
        $feedback = \DB::table('driver_feedback')
            ->where('dboy_id', $this->driver->dboy_id)
            ->where('subject', 'Timestamp Test')
            ->first();

        $this->assertNotNull($feedback);
        $this->assertNotNull($feedback->created_at ?? $feedback->feedback_date);

        // Verify timestamp is recent
        $feedbackTime = \Carbon\Carbon::parse($feedback->created_at ?? $feedback->feedback_date);
        $this->assertTrue($feedbackTime->between($beforeSubmission, $afterSubmission));
    }

    /**
     * Test feedback priority levels
     */
    public function test_feedback_priority_levels(): void
    {
        $urgentResponse = $this->driverApiCall('POST', 'driver_feedback', [
            'dboy_id' => $this->driver->dboy_id,
            'message' => 'URGENT: Cannot access the app, stuck with multiple orders',
            'subject' => 'URGENT - App Access Issue',
        ]);

        $this->assertApiSuccess($urgentResponse, 'Thank you for your feedback');

        $normalResponse = $this->driverApiCall('POST', 'driver_feedback', [
            'dboy_id' => $this->driver->dboy_id,
            'message' => 'Suggestion for better UI design',
            'subject' => 'UI Improvement Suggestion',
        ]);

        $this->assertApiSuccess($normalResponse, 'Thank you for your feedback');

        // Verify both feedback types were stored
        $this->assertDatabaseHas('driver_feedback', [
            'dboy_id' => $this->driver->dboy_id,
            'subject' => 'URGENT - App Access Issue',
        ]);

        $this->assertDatabaseHas('driver_feedback', [
            'dboy_id' => $this->driver->dboy_id,
            'subject' => 'UI Improvement Suggestion',
        ]);
    }

    /**
     * Test feedback with special characters
     */
    public function test_feedback_with_special_characters(): void
    {
        $messageWithSpecialChars = 'Order #12345 - Customer paid ₹500 but app shows ₹450. Difference: ₹50. Please check & resolve ASAP!';

        $response = $this->driverApiCall('POST', 'driver_feedback', [
            'dboy_id' => $this->driver->dboy_id,
            'message' => $messageWithSpecialChars,
            'subject' => 'Payment Discrepancy ₹50',
        ]);

        $this->assertApiSuccess($response, 'Thank you for your feedback');

        // Verify feedback with special characters was stored
        $this->assertDatabaseHas('driver_feedback', [
            'dboy_id' => $this->driver->dboy_id,
            'subject' => 'Payment Discrepancy ₹50',
        ]);
    }
}
