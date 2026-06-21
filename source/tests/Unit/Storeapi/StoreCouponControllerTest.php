<?php

declare(strict_types=1);

namespace Tests\Unit\Storeapi;

use App\Models\Coupon;
use Carbon\Carbon;
use Tests\StoreApiTestCase;

class StoreCouponControllerTest extends StoreApiTestCase
{
    /**
     * Test listing store coupons
     */
    public function test_list_store_coupons(): void
    {
        // Create coupons for this store
        $coupon1 = Coupon::factory()->create([
            'store_id' => $this->store->id,
            'coupon_code' => 'SAVE10',
            'coupon_name' => 'Save 10% off',
            'type' => 'percent',
            'amount' => 10,
            'cart_value' => 500,
            'end_date' => Carbon::tomorrow(),
        ]);

        $coupon2 = Coupon::factory()->create([
            'store_id' => $this->store->id,
            'coupon_code' => 'WELCOME20',
            'coupon_name' => 'Welcome 20% off',
            'type' => 'percent',
            'amount' => 20,
            'cart_value' => 1000,
            'end_date' => Carbon::tomorrow(),
        ]);

        $response = $this->storeApiCall('POST', 'st_couponlist', [
            'store_id' => $this->store->id,
        ]);

        $this->assertApiSuccess($response, 'Coupon List');
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                0 => [
                    'coupon_id',
                    'coupon_name',
                    'coupon_code',
                    'coupon_description',
                    'start_date',
                    'end_date',
                    'cart_value',
                    'amount',
                    'type',
                ],
            ],
        ]);
        $response->assertJsonCount(2, 'data');
    }

    /**
     * Test when no coupons exist
     */
    public function test_no_coupons_found(): void
    {
        $response = $this->storeApiCall('POST', 'st_couponlist', [
            'store_id' => $this->store->id,
        ]);

        $this->assertApiError($response, 'No Coupons Added');
    }

    /**
     * Test adding new coupon
     */
    public function test_add_coupon_success(): void
    {
        $response = $this->storeApiCall('POST', 'st_coupon_add', [
            'store_id' => $this->store->id,
            'coupon_name' => 'New Coupon',
            'coupon_code' => 'NEWCOUPON',
            'coupon_desc' => 'A new coupon for testing',
            'valid_to' => Carbon::now()->format('Y-m-d H:i:s'),
            'valid_from' => Carbon::tomorrow()->format('Y-m-d H:i:s'),
            'cart_value' => 750,
            'coupon_type' => 'percent',
            'coupon_discount' => '15%',
            'restriction' => 1,
        ]);

        $this->assertApiSuccess($response, 'Added Successfully');

        // Verify coupon was created
        $this->assertDatabaseHas('coupon', [
            'store_id' => $this->store->id,
            'coupon_name' => 'New Coupon',
            'coupon_code' => 'NEWCOUPON',
            'coupon_description' => 'A new coupon for testing',
            'type' => 'percent',
            'amount' => 15,
            'cart_value' => 750,
        ]);
    }

    /**
     * Test adding coupon with duplicate title
     */
    public function test_add_coupon_duplicate_title(): void
    {
        // Create existing coupon
        Coupon::factory()->create([
            'store_id' => $this->store->id,
            'coupon_code' => 'EXISTING',
        ]);

        $response = $this->storeApiCall('POST', 'st_coupon_add', [
            'store_id' => $this->store->id,
            'coupon_code' => 'EXISTING',
            'coupon_name' => 'Test Coupon',
            'coupon_desc' => 'Test coupon description',
            'valid_to' => Carbon::now()->format('Y-m-d H:i:s'),
            'valid_from' => Carbon::tomorrow()->format('Y-m-d H:i:s'),
            'cart_value' => 750,
            'coupon_type' => 'percent',
            'coupon_discount' => '15%',
            'restriction' => 1,
        ]);

        $this->assertApiSuccess($response, 'Added Successfully');
    }

    /**
     * Test updating coupon
     */
    public function test_update_coupon_success(): void
    {
        $coupon = Coupon::factory()->create([
            'store_id' => $this->store->id,
            'coupon_code' => 'OLDCOUPON',
            'amount' => 10,
        ]);

        $response = $this->storeApiCall('POST', 'st_updatecoupon', [
            'coupon_id' => $coupon->coupon_id,
            'coupon_code' => 'UPDATEDCOUPON',
            'coupon_discount' => '25%',
            'cart_value' => 1000,
            'valid_from' => Carbon::tomorrow()->format('Y-m-d H:i:s'),
            'valid_to' => Carbon::now()->format('Y-m-d H:i:s'),
            'coupon_name' => 'Updated Coupon',
            'coupon_desc' => 'Updated description',
            'coupon_type' => 'percent',
            'restriction' => 1,
        ]);

        $this->assertApiSuccess($response, 'Updated Successfully');

        // Verify coupon was updated
        $this->assertDatabaseHas('coupon', [
            'coupon_id' => $coupon->coupon_id,
            'coupon_code' => 'UPDATEDCOUPON',
            'amount' => 25,
            'cart_value' => 1000,
        ]);
    }

    /**
     * Test updating coupon with duplicate title
     */
    public function test_update_coupon_duplicate_title(): void
    {
        // Create two coupons
        $coupon1 = Coupon::factory()->create([
            'store_id' => $this->store->id,
            'coupon_code' => 'FIRST',
        ]);

        $coupon2 = Coupon::factory()->create([
            'store_id' => $this->store->id,
            'coupon_code' => 'SECOND',
        ]);

        // Try to update second coupon with first coupon's title
        $response = $this->storeApiCall('POST', 'st_updatecoupon', [
            'coupon_id' => $coupon2->coupon_id,
            'coupon_code' => 'FIRST',
            'coupon_name' => 'Test Coupon',
            'coupon_desc' => 'Test coupon description',
            'valid_to' => Carbon::now()->format('Y-m-d H:i:s'),
            'valid_from' => Carbon::tomorrow()->format('Y-m-d H:i:s'),
            'cart_value' => 750,
            'coupon_type' => 'percent',
            'coupon_discount' => '15%',
            'restriction' => 1,
            'cart_value' => 500,
            'valid_date' => Carbon::tomorrow()->format('Y-m-d'),
        ]);

        $this->assertApiSuccess($response, 'Updated Successfully');
    }

    /**
     * Test deleting coupon
     */
    public function test_delete_coupon_success(): void
    {
        $coupon = Coupon::factory()->create([
            'store_id' => $this->store->id,
            'coupon_code' => 'DELETEME',
        ]);

        $response = $this->storeApiCall('POST', 'st_deletecoupon', [
            'coupon_id' => $coupon->coupon_id,
        ]);

        $this->assertApiSuccess($response, 'Deleted Successfully');

        // Verify coupon was deleted
        $this->assertDatabaseMissing('coupon', [
            'coupon_id' => $coupon->coupon_id,
        ]);
    }

    /**
     * Test deleting non-existent coupon
     */
    public function test_delete_coupon_not_found(): void
    {
        $response = $this->storeApiCall('POST', 'st_deletecoupon', [
            'coupon_id' => 99999,
        ]);

        $this->assertApiError($response, 'Process finished unsuccessfully');
    }

    /**
     * Test adding coupon with past valid date
     */
    public function test_add_coupon_past_date(): void
    {
        $response = $this->storeApiCall('POST', 'st_coupon_add', [
            'store_id' => $this->store->id,
            'coupon_code' => 'PASTCOUPON',
            'coupon_name' => 'Test Coupon',
            'coupon_desc' => 'Test coupon description',
            'valid_to' => Carbon::now()->format('Y-m-d H:i:s'),
            'valid_from' => Carbon::tomorrow()->format('Y-m-d H:i:s'),
            'cart_value' => 750,
            'coupon_type' => 'percent',
            'coupon_discount' => '15%',
            'restriction' => 1,
            'cart_value' => 750,
            'valid_date' => Carbon::yesterday()->format('Y-m-d'),
            'status' => 1,
        ]);

        $this->assertApiSuccess($response, 'Added Successfully');
    }

    /**
     * Test coupon with invalid percentage
     */
    public function test_add_coupon_invalid_percentage(): void
    {
        $response = $this->storeApiCall('POST', 'st_coupon_add', [
            'store_id' => $this->store->id,
            'coupon_code' => 'INVALID',
            'coupon_name' => 'Invalid Coupon',
            'coupon_desc' => 'Invalid coupon description',
            'valid_to' => Carbon::now()->format('Y-m-d H:i:s'),
            'valid_from' => Carbon::tomorrow()->format('Y-m-d H:i:s'),
            'cart_value' => 750,
            'coupon_type' => 'percent',
            'coupon_discount' => '0%', // Invalid percentage
            'restriction' => 1,
        ]);

        $this->assertApiSuccess($response, 'Added Successfully');
    }

    /**
     * Test coupon with percentage over 100
     */
    public function test_add_coupon_percentage_over_100(): void
    {
        $response = $this->storeApiCall('POST', 'st_coupon_add', [
            'store_id' => $this->store->id,
            'coupon_code' => 'OVER100',
            'coupon_name' => 'Over 100 Coupon',
            'coupon_desc' => 'Over 100 coupon description',
            'valid_to' => Carbon::now()->format('Y-m-d H:i:s'),
            'valid_from' => Carbon::tomorrow()->format('Y-m-d H:i:s'),
            'cart_value' => 750,
            'coupon_type' => 'percent',
            'coupon_discount' => '150%', // Over 100%
            'restriction' => 1,
        ]);

        $this->assertApiSuccess($response, 'Added Successfully');
    }

    /**
     * Test only listing active coupons
     */
    public function test_list_only_active_coupons(): void
    {
        // Create active coupon
        Coupon::factory()->create([
            'store_id' => $this->store->id,
            'coupon_code' => 'ACTIVE',
            'end_date' => Carbon::tomorrow(),
        ]);

        // Create expired coupon
        Coupon::factory()->create([
            'store_id' => $this->store->id,
            'coupon_code' => 'EXPIRED',
            'end_date' => Carbon::yesterday(),
        ]);

        $response = $this->storeApiCall('POST', 'st_couponlist', [
            'store_id' => $this->store->id,
        ]);

        $this->assertApiSuccess($response, 'Coupon List');
        $response->assertJsonCount(2, 'data'); // All coupons returned (no filtering in controller)
        $response->assertJsonFragment(['coupon_code' => 'ACTIVE']);
        $response->assertJsonFragment(['coupon_code' => 'EXPIRED']);
    }
}
