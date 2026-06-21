<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Controllers\Api\MapsetController;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MapsetControllerTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    protected MapsetController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new MapsetController;

        // Create test tables for SQLite
        DB::statement('CREATE TABLE IF NOT EXISTS map_settings (
            id INTEGER PRIMARY KEY,
            map_type TEXT,
            status INTEGER,
            created_at TEXT,
            updated_at TEXT
        )');

        DB::statement('CREATE TABLE IF NOT EXISTS map_api (
            id INTEGER PRIMARY KEY,
            google_map_api TEXT,
            created_at TEXT,
            updated_at TEXT
        )');

        DB::statement('CREATE TABLE IF NOT EXISTS mapbox (
            id INTEGER PRIMARY KEY,
            mapbox_access_token TEXT,
            created_at TEXT,
            updated_at TEXT
        )');
    }

    protected function tearDown(): void
    {
        // Clean up test data to ensure fresh state for each test
        DB::statement('DELETE FROM map_settings WHERE 1=1');
        DB::statement('DELETE FROM map_api WHERE 1=1');
        DB::statement('DELETE FROM mapbox WHERE 1=1');

        parent::tearDown();
    }

    /**
     * Test mapby method with data
     */
    public function test_mapby_with_data(): void
    {
        // Create map settings record
        DB::table('map_settings')->insert([
            'map_type' => 'google',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = new Request;

        $response = $this->controller->mapby($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('map and places via', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('google', $response['data']->map_type);
    }

    /**
     * Test mapby method without data
     */
    public function test_mapby_without_data(): void
    {
        $request = new Request;

        $response = $this->controller->mapby($request);

        $this->assertEquals('0', $response['status']);
        $this->assertEquals('data not found', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertEmpty($response['data']);
    }

    /**
     * Test google_map method with data
     */
    public function test_google_map_with_data(): void
    {
        // Create map API record
        DB::table('map_api')->insert([
            'google_map_api' => 'test_api_key_123',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = new Request;

        $response = $this->controller->google_map($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Google map api', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('test_api_key_123', $response['data']->google_map_api);
    }

    /**
     * Test google_map method without data
     */
    public function test_google_map_without_data(): void
    {
        $request = new Request;

        $response = $this->controller->google_map($request);

        $this->assertEquals('0', $response['status']);
        $this->assertEquals('data not found', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertEmpty($response['data']);
    }

    /**
     * Test mapbox method with data
     */
    public function test_mapbox_with_data(): void
    {
        // Create mapbox record
        DB::table('mapbox')->insert([
            'mapbox_access_token' => 'pk.test_mapbox_token',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = new Request;

        $response = $this->controller->mapbox($request);

        $this->assertEquals('1', $response['status']);
        $this->assertEquals('Mapbox api', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('pk.test_mapbox_token', $response['data']->mapbox_access_token);
    }

    /**
     * Test mapbox method without data
     */
    public function test_mapbox_without_data(): void
    {
        $request = new Request;

        $response = $this->controller->mapbox($request);

        $this->assertEquals('0', $response['status']);
        $this->assertEquals('data not found', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertEmpty($response['data']);
    }

    /**
     * Test data structure consistency across methods
     */
    public function test_data_structure_consistency(): void
    {
        $request = new Request;

        // Test all methods return consistent structure
        $mapbyResponse = $this->controller->mapby($request);
        $googleMapResponse = $this->controller->google_map($request);
        $mapboxResponse = $this->controller->mapbox($request);

        // All responses should have status, message, and data keys
        foreach ([$mapbyResponse, $googleMapResponse, $mapboxResponse] as $response) {
            $this->assertArrayHasKey('status', $response);
            $this->assertArrayHasKey('message', $response);
            $this->assertArrayHasKey('data', $response);
        }
    }
}
