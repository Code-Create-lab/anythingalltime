<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Controllers\Api\PagesController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class PagesControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected PagesController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new PagesController;

        // Create test tables using Laravel Schema builder for compatibility
        if (!Schema::hasTable('aboutuspage')) {
            Schema::create('aboutuspage', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title')->nullable();
                $table->text('content')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('termspage')) {
            Schema::create('termspage', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title')->nullable();
                $table->text('content')->nullable();
                $table->timestamps();
            });
        }
    }

    protected function tearDown(): void
    {
        // Clean up test data
        if (Schema::hasTable('aboutuspage')) {
            DB::table('aboutuspage')->truncate();
        }
        if (Schema::hasTable('termspage')) {
            DB::table('termspage')->truncate();
        }

        parent::tearDown();
    }

    /**
     * Test appaboutus method with data
     */
    public function test_appaboutus_with_data(): void
    {
        // Create about us page record
        DB::table('aboutuspage')->insert([
            'title' => 'About Anything Alltime',
            'content' => 'Welcome to Anything Alltime, your one-stop grocery solution.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = new Request;

        $response = $this->controller->appaboutus($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('About us', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('About Anything Alltime', $response['data']->title);
        $this->assertEquals('Welcome to Anything Alltime, your one-stop grocery solution.', $response['data']->content);
    }

    /**
     * Test appaboutus method without data
     */
    public function test_appaboutus_without_data(): void
    {
        $request = new Request;

        $response = $this->controller->appaboutus($request);

        $this->assertEquals('0', $response['status']);
        $this->assertEquals('data not found', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertEmpty($response['data']);
    }

    /**
     * Test appterms method with data
     */
    public function test_appterms_with_data(): void
    {
        // Create terms page record
        DB::table('termspage')->insert([
            'title' => 'Terms and Conditions',
            'content' => 'These are the terms and conditions for using Anything Alltime.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = new Request;

        $response = $this->controller->appterms($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Terms & Condition', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('Terms and Conditions', $response['data']->title);
        $this->assertEquals('These are the terms and conditions for using Anything Alltime.', $response['data']->content);
    }

    /**
     * Test appterms method without data
     */
    public function test_appterms_without_data(): void
    {
        $request = new Request;

        $response = $this->controller->appterms($request);

        $this->assertEquals('0', $response['status']);
        $this->assertEquals('data not found', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertEmpty($response['data']);
    }

    /**
     * Test response structure consistency
     */
    public function test_response_structure_consistency(): void
    {
        $request = new Request;

        // Test both methods return consistent structure
        $aboutResponse = $this->controller->appaboutus($request);
        $termsResponse = $this->controller->appterms($request);

        foreach ([$aboutResponse, $termsResponse] as $response) {
            $this->assertArrayHasKey('status', $response);
            $this->assertArrayHasKey('message', $response);
            $this->assertArrayHasKey('data', $response);
        }
    }

    /**
     * Test with HTML content in pages
     */
    public function test_pages_with_html_content(): void
    {
        // Create page with HTML content
        DB::table('aboutuspage')->insert([
            'title' => 'About Us with HTML',
            'content' => '<h1>Welcome to Anything Alltime</h1><p>We are a <strong>modern</strong> grocery delivery service.</p>',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = new Request;

        $response = $this->controller->appaboutus($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('About us', $response['message']);
        $this->assertStringContainsString('<h1>Welcome to Anything Alltime</h1>', $response['data']->content);
        $this->assertStringContainsString('<strong>modern</strong>', $response['data']->content);
    }

    /**
     * Test with special characters in content
     */
    public function test_pages_with_special_characters(): void
    {
        // Create page with special characters
        DB::table('termspage')->insert([
            'title' => 'Terms & Conditions with Special Characters',
            'content' => 'Price: $10.99 per item. Discount: 20% off. Note: "Free delivery" applies.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = new Request;

        $response = $this->controller->appterms($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Terms & Condition', $response['message']);
        $this->assertStringContainsString('$10.99', $response['data']->content);
        $this->assertStringContainsString('20%', $response['data']->content);
        $this->assertStringContainsString('"Free delivery"', $response['data']->content);
    }

    /**
     * Test with empty content
     */
    public function test_pages_with_empty_content(): void
    {
        // Create page with empty content
        DB::table('aboutuspage')->insert([
            'title' => 'Empty About Page',
            'content' => '',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = new Request;

        $response = $this->controller->appaboutus($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('About us', $response['message']);
        $this->assertEquals('Empty About Page', $response['data']->title);
        $this->assertEquals('', $response['data']->content);
    }

    /**
     * Test with null title and content
     */
    public function test_pages_with_null_fields(): void
    {
        // Create page with null fields
        DB::table('termspage')->insert([
            'title' => null,
            'content' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = new Request;

        $response = $this->controller->appterms($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Terms & Condition', $response['message']);
        $this->assertNull($response['data']->title);
        $this->assertNull($response['data']->content);
    }

    /**
     * Test data structure and types
     */
    public function test_data_structure_and_types(): void
    {
        // Create both pages
        DB::table('aboutuspage')->insert([
            'title' => 'Test About',
            'content' => 'Test content',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('termspage')->insert([
            'title' => 'Test Terms',
            'content' => 'Test terms content',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = new Request;

        // Test about us data structure
        $aboutResponse = $this->controller->appaboutus($request);
        $this->assertIsObject($aboutResponse['data']);
        $this->assertObjectHasProperty('id', $aboutResponse['data']);
        $this->assertObjectHasProperty('title', $aboutResponse['data']);
        $this->assertObjectHasProperty('content', $aboutResponse['data']);

        // Test terms data structure
        $termsResponse = $this->controller->appterms($request);
        $this->assertIsObject($termsResponse['data']);
        $this->assertObjectHasProperty('id', $termsResponse['data']);
        $this->assertObjectHasProperty('title', $termsResponse['data']);
        $this->assertObjectHasProperty('content', $termsResponse['data']);
    }
}
