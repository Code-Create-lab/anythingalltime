<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Controllers\Api\PassportController;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tests\TestCase;

class PassportControllerTest extends TestCase
{
    use WithFaker;

    protected PassportController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new PassportController;
    }

    /**
     * Test validates method returns 401 unauthorized
     */
    public function test_validates_returns_unauthorized(): void
    {
        $request = new Request;

        $response = $this->controller->validates($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(401, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('UnAuthorised', $responseData['error']);
    }

    /**
     * Test validates method with various request parameters
     */
    public function test_validates_ignores_request_parameters(): void
    {
        $request = new Request([
            'token' => 'test-token',
            'user_id' => 123,
            'email' => 'test@example.com',
            'anything' => 'value',
        ]);

        $response = $this->controller->validates($request);

        // Should still return 401 regardless of parameters
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(401, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('UnAuthorised', $responseData['error']);
    }

    /**
     * Test validates method with empty request
     */
    public function test_validates_with_empty_request(): void
    {
        $request = new Request;

        $response = $this->controller->validates($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(401, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('UnAuthorised', $responseData['error']);
    }

    /**
     * Test response structure consistency
     */
    public function test_response_structure(): void
    {
        $request = new Request;

        $response = $this->controller->validates($request);
        $responseData = json_decode($response->getContent(), true);

        // Verify response structure
        $this->assertIsArray($responseData);
        $this->assertCount(1, $responseData);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertIsString($responseData['error']);
    }

    /**
     * Test content type is JSON
     */
    public function test_response_content_type(): void
    {
        $request = new Request;

        $response = $this->controller->validates($request);

        // Check that response is JSON
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    /**
     * Test multiple calls return same result
     */
    public function test_validates_consistency(): void
    {
        $request = new Request;

        $response1 = $this->controller->validates($request);
        $response2 = $this->controller->validates($request);

        // Both responses should be identical
        $this->assertEquals($response1->getStatusCode(), $response2->getStatusCode());
        $this->assertEquals($response1->getContent(), $response2->getContent());
    }

    /**
     * Test controller behavior with different HTTP methods simulation
     */
    public function test_validates_with_different_simulated_methods(): void
    {
        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];

        foreach ($methods as $method) {
            // Simulate different methods by setting the method override
            $request = new Request;
            $request->setMethod($method);

            $response = $this->controller->validates($request);

            // Should always return 401 regardless of method
            $this->assertEquals(401, $response->getStatusCode());

            $responseData = json_decode($response->getContent(), true);
            $this->assertEquals('UnAuthorised', $responseData['error']);
        }
    }
}
