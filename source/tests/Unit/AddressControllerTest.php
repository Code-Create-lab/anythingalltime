<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Controllers\Api\AddressController;
use App\Models\Address;
use App\Models\Cities;
use App\Models\Orders;
use App\Models\ServiceArea;
use App\Models\Stores;
use App\Models\Town;
use App\Models\User;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Tests\TestCase;

class AddressControllerTest extends TestCase
{
    use WithFaker;

    protected AddressController $controller;

    protected User $user;

    protected Cities $city;

    protected Town $society;

    protected Stores $store;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new AddressController;

        // Create test user
        $this->user = User::factory()->create([
            'user_phone' => '1234567890',
        ]);

        // Create test city
        $this->city = Cities::factory()->create([
            'city_id' => 1,
            'city_name' => 'Test City',
        ]);

        // Create test society
        $this->society = Town::factory()->create([
            'society_id' => 1,
            'society_name' => 'Test Society',
            'city_id' => 1,
        ]);

        // Create test store
        $this->store = Stores::factory()->create([
            'store_name' => 'Test Store',
            'lat' => '12.9716',
            'lng' => '77.5946',
            'del_range' => 10,
        ]);
    }

    /**
     * Test saving a new address
     */
    public function test_address_save_new(): void
    {
        $request = new Request([
            'user_id' => $this->user->id,
            'type' => 'Home',
            'receiver_name' => 'John Doe',
            'receiver_phone' => '1234567890',
            'city_name' => 'Test City',
            'society_name' => 'Test Society',
            'house_no' => '123',
            'landmark' => 'Near Park',
            'state' => 'Test State',
            'pin' => '123456',
            'lat' => '12.9716',
            'lng' => '77.5946',
        ]);

        $response = $this->controller->address($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('1', $responseData['status']);
        $this->assertEquals('Address Saved', $responseData['message']);

        // Verify address was created
        $this->assertDatabaseHas('address', [
            'user_id' => $this->user->id,
            'type' => 'Home',
            'receiver_name' => 'John Doe',
            'select_status' => 1,
        ]);
    }

    /**
     * Test updating existing address
     */
    public function test_address_update_existing(): void
    {
        // Create existing address
        Address::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'Home',
            'receiver_name' => 'Old Name',
            'select_status' => 0,
        ]);

        $request = new Request([
            'user_id' => $this->user->id,
            'type' => 'Home',
            'receiver_name' => 'Updated Name',
            'receiver_phone' => '1234567890',
            'city_name' => 'Test City',
            'society_name' => 'Test Society',
            'house_no' => '123',
            'landmark' => 'Near Park',
            'state' => 'Test State',
            'pin' => '123456',
            'lat' => '12.9716',
            'lng' => '77.5946',
        ]);

        $response = $this->controller->address($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('1', $responseData['status']);
        $this->assertEquals('Address Saved', $responseData['message']);

        // Verify address was updated
        $this->assertDatabaseHas('address', [
            'user_id' => $this->user->id,
            'type' => 'Home',
            'receiver_name' => 'Updated Name',
            'select_status' => 1,
        ]);
    }

    /**
     * Test address with invalid city
     */
    public function test_address_invalid_city(): void
    {
        $request = new Request([
            'user_id' => $this->user->id,
            'type' => 'Home',
            'city_name' => 'Invalid City',
            'society_name' => 'Test Society',
        ]);

        $response = $this->controller->address($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('0', $responseData['status']);
        $this->assertEquals('Invalid city or society', $responseData['message']);
    }

    /**
     * Test address with invalid society
     */
    public function test_address_invalid_society(): void
    {
        $request = new Request([
            'user_id' => $this->user->id,
            'type' => 'Home',
            'city_name' => 'Test City',
            'society_name' => 'Invalid Society',
        ]);

        $response = $this->controller->address($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('0', $responseData['status']);
        $this->assertEquals('Invalid city or society', $responseData['message']);
    }

    /**
     * Test getting cities list
     */
    public function test_city_list(): void
    {
        $request = new Request;

        $response = $this->controller->city($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('1', $responseData['status']);
        $this->assertEquals('City list', $responseData['message']);
        $this->assertArrayHasKey('data', $responseData);
    }

    /**
     * Test getting societies for a city
     */
    public function test_society_list(): void
    {
        $request = new Request(['city_id' => 1]);

        $response = $this->controller->society($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('1', $responseData['status']);
        $this->assertEquals('Society list', $responseData['message']);
        $this->assertArrayHasKey('data', $responseData);
    }

    /**
     * Test getting societies for invalid city
     */
    public function test_society_list_invalid_city(): void
    {
        $request = new Request(['city_id' => 999]);

        $response = $this->controller->society($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('0', $responseData['status']);
        $this->assertEquals('Society not found', $responseData['message']);
    }

    /**
     * Test showing addresses within delivery range
     */
    public function test_show_address_within_range(): void
    {
        // Create address within delivery range
        Address::factory()->create([
            'user_id' => $this->user->id,
            'lat' => '12.9716',
            'lng' => '77.5946',
            'select_status' => 1,
        ]);

        $request = new Request([
            'user_id' => $this->user->id,
            'store_id' => $this->store->id,
        ]);

        $response = $this->controller->show_address($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('1', $responseData['status']);
        $this->assertEquals('Address list', $responseData['message']);
        $this->assertArrayHasKey('data', $responseData);
    }

    /**
     * Test showing addresses outside delivery range
     */
    public function test_show_address_outside_range(): void
    {
        $this->markTestIncomplete('Distance calculation needs investigation for SQLite compatibility');

        // Create address outside delivery range (extremely far from store)
        Address::factory()->create([
            'user_id' => $this->user->id,
            'lat' => '50.0', // Extremely far from store at 12.9716
            'lng' => '100.0', // Extremely far from store at 77.5946
            'select_status' => 1,
        ]);

        $request = new Request([
            'user_id' => $this->user->id,
            'store_id' => $this->store->id,
        ]);

        $response = $this->controller->show_address($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('0', $responseData['status']);
        $this->assertEquals('No addresses found! Please add', $responseData['message']);
    }

    /**
     * Test showing addresses for invalid store
     */
    public function test_show_address_invalid_store(): void
    {
        $request = new Request([
            'user_id' => $this->user->id,
            'store_id' => 999,
        ]);

        $response = $this->controller->show_address($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('0', $responseData['status']);
        $this->assertEquals('Store not found', $responseData['message']);
    }

    /**
     * Test selecting an address
     */
    public function test_select_address(): void
    {
        // Create address
        $address = Address::factory()->create([
            'user_id' => $this->user->id,
            'select_status' => 0,
        ]);

        $request = new Request(['address_id' => $address->address_id]);

        $response = $this->controller->select_address($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('1', $responseData['status']);
        $this->assertEquals('Address Selected', $responseData['message']);

        // Verify address was selected
        $this->assertDatabaseHas('address', [
            'address_id' => $address->address_id,
            'select_status' => 1,
        ]);
    }

    /**
     * Test selecting invalid address
     */
    public function test_select_invalid_address(): void
    {
        $request = new Request(['address_id' => 999]);

        $response = $this->controller->select_address($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('0', $responseData['status']);
        $this->assertEquals('Address not found', $responseData['message']);
    }

    /**
     * Test removing address without orders
     */
    public function test_remove_address_no_orders(): void
    {
        // Create address
        $address = Address::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $request = new Request(['address_id' => $address->address_id]);

        $response = $this->controller->rem_user_address($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('1', $responseData['status']);
        $this->assertEquals('Address Removed', $responseData['message']);

        // Verify address was deleted
        $this->assertDatabaseMissing('address', [
            'address_id' => $address->address_id,
        ]);
    }

    /**
     * Test removing address with orders (soft delete)
     */
    public function test_remove_address_with_orders(): void
    {
        // Create address
        $address = Address::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Create order using this address
        Orders::factory()->create([
            'address_id' => $address->address_id,
        ]);

        $request = new Request(['address_id' => $address->address_id]);

        $response = $this->controller->rem_user_address($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('1', $responseData['status']);
        $this->assertEquals('Address Removed', $responseData['message']);

        // Verify address was soft deleted
        $this->assertDatabaseHas('address', [
            'address_id' => $address->address_id,
            'select_status' => 2,
        ]);
    }

    /**
     * Test removing invalid address
     */
    public function test_remove_invalid_address(): void
    {
        $request = new Request(['address_id' => 999]);

        $response = $this->controller->rem_user_address($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('0', $responseData['status']);
        $this->assertEquals('Address not found', $responseData['message']);
    }

    /**
     * Test editing address
     */
    public function test_edit_address(): void
    {
        // Create address with select_status = 0 initially
        $address = Address::factory()->create([
            'user_id' => $this->user->id,
            'receiver_name' => 'Old Name',
            'select_status' => 0,
        ]);

        $request = new Request([
            'address_id' => $address->address_id,
            'user_id' => $this->user->id,
            'receiver_name' => 'Updated Name',
            'receiver_phone' => '1234567890',
            'city_name' => 'Test City',
            'society_name' => 'Test Society',
            'house_no' => '123',
            'landmark' => 'Near Park',
            'state' => 'Test State',
            'pin' => '123456',
            'lat' => '12.9716',
            'lng' => '77.5946',
            'type' => 'Home',
        ]);

        $response = $this->controller->edit_add($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('1', $responseData['status']);
        $this->assertEquals('Address Saved', $responseData['message']);

        // Verify address was updated
        $this->assertDatabaseHas('address', [
            'address_id' => $address->address_id,
            'receiver_name' => 'Updated Name',
            'select_status' => 1,
        ]);
    }

    /**
     * Test editing invalid address
     */
    public function test_edit_invalid_address(): void
    {
        $request = new Request([
            'address_id' => 999,
            'user_id' => $this->user->id,
            'city_name' => 'Test City',
            'society_name' => 'Test Society',
        ]);

        $response = $this->controller->edit_add($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('0', $responseData['status']);
        $this->assertEquals('Address not found', $responseData['message']);
    }

    /**
     * Test showing all addresses grouped by type
     */
    public function test_show_all_addresses(): void
    {
        // Create addresses of different types
        Address::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'Home',
            'select_status' => 1,
        ]);

        Address::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'Office',
            'select_status' => 0,
        ]);

        $request = new Request(['user_id' => $this->user->id]);

        $response = $this->controller->show_all_address($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertIsArray($responseData);
        $this->assertGreaterThan(0, count($responseData));
    }

    /**
     * Test showing all addresses when none exist
     */
    public function test_show_all_addresses_empty(): void
    {
        $request = new Request(['user_id' => $this->user->id]);

        $response = $this->controller->show_all_address($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertIsArray($responseData);
        $this->assertEquals('No Address Found', $responseData[0]['data']);
    }

    /**
     * Test getting societies for store service area
     */
    public function test_society_for_store(): void
    {
        // Create service area
        ServiceArea::factory()->create([
            'store_id' => $this->store->id,
            'society_name' => 'Test Society',
            'society_id' => 1,
        ]);

        $request = new Request(['store_id' => $this->store->id]);

        $response = $this->controller->societyforadd($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('1', $responseData['status']);
        $this->assertEquals('Society list', $responseData['message']);
        $this->assertArrayHasKey('data', $responseData);
    }

    /**
     * Test getting societies for store with no service area
     */
    public function test_society_for_store_no_service_area(): void
    {
        $request = new Request(['store_id' => 999]);

        $response = $this->controller->societyforadd($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals('0', $responseData['status']);
        $this->assertEquals('Society not found', $responseData['message']);
    }
}
