<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Cities;
use App\Models\Orders;
use App\Models\ServiceArea;
use App\Models\Stores;
use App\Models\Town;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    /**
     * Save or update user address
     */
    public function address(Request $request): JsonResponse
    {
        $user_id = $request->user_id;
        $type = $request->type;

        // Reset all addresses to unselected
        Address::where('user_id', $user_id)->update(['select_status' => 0]);

        // Get city and society IDs
        $city = Cities::where('city_name', $request->city_name)->first();
        $society = Town::where('society_name', $request->society_name)->first();

        if (! $city || ! $society) {
            return response()->json([
                'status' => '0',
                'message' => 'Invalid city or society',
            ]);
        }

        $addressData = [
            'user_id' => $user_id,
            'receiver_name' => $request->receiver_name,
            'receiver_phone' => $request->receiver_phone,
            'city' => $request->city_name,
            'society' => $request->society_name,
            'city_id' => $city->city_id,
            'society_id' => $society->society_id,
            'house_no' => $request->house_no,
            'landmark' => $request->landmark,
            'state' => $request->state,
            'pincode' => $request->pin,
            'select_status' => 1,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'type' => $type === 'Others' ? 'Others' : $type,
            'added_at' => Carbon::now(),
        ];

        if ($type === 'Others') {
            // Create new address for "Others" type
            $address = Address::create($addressData);
        } else {
            // Update existing address or create new one for specific types
            $existingAddress = Address::where('user_id', $user_id)
                ->where('type', $type)
                ->first();

            if ($existingAddress) {
                $existingAddress->update($addressData);
                $address = $existingAddress;
            } else {
                $address = Address::create($addressData);
            }
        }

        if ($address) {
            return response()->json([
                'status' => '1',
                'message' => trans('keywords.Address Saved'),
            ]);
        }

        return response()->json([
            'status' => '0',
            'message' => 'Something went wrong',
        ]);
    }

    /**
     * Get list of cities with societies
     */
    public function city(Request $request): JsonResponse
    {
        $cities = Cities::with('societies')
            ->whereHas('societies')
            ->select('city_id', 'city_name')
            ->get();

        if ($cities->count() > 0) {
            return response()->json([
                'status' => '1',
                'message' => 'City list',
                'data' => $cities,
            ]);
        }

        return response()->json([
            'status' => '0',
            'message' => 'City not found',
        ]);
    }

    /**
     * Get societies for a specific city
     */
    public function society(Request $request): JsonResponse
    {
        $city_id = $request->city_id;

        $societies = Town::with('city')
            ->where('city_id', $city_id)
            ->get();

        if ($societies->count() > 0) {
            return response()->json([
                'status' => '1',
                'message' => 'Society list',
                'data' => $societies,
            ]);
        }

        return response()->json([
            'status' => '0',
            'message' => 'Society not found',
        ]);
    }

    /**
     * Show addresses within delivery range of a store
     */
    public function show_address(Request $request): JsonResponse
    {
        $user_id = $request->user_id;
        $store_id = $request->store_id;

        $store = Stores::find($store_id);

        if (! $store) {
            return response()->json([
                'status' => '0',
                'message' => 'Store not found',
            ]);
        }

        $addresses = Address::active()
            ->where('user_id', $user_id)
            ->withinDeliveryRange($store->lat, $store->lng, $store->del_range)
            ->get();

        if ($addresses->count() > 0) {
            return response()->json([
                'status' => '1',
                'message' => 'Address list',
                'data' => $addresses,
            ]);
        }

        return response()->json([
            'status' => '0',
            'message' => 'No addresses found! Please add',
        ]);
    }

    /**
     * Select an address as default
     */
    public function select_address(Request $request): JsonResponse
    {
        $address_id = $request->address_id;

        $address = Address::find($address_id);

        if (! $address) {
            return response()->json([
                'status' => '0',
                'message' => 'Address not found',
            ]);
        }

        // Reset all addresses for this user
        Address::where('user_id', $address->user_id)
            ->update(['select_status' => 0]);

        // Select the specified address
        $address->update(['select_status' => 1]);

        return response()->json([
            'status' => '1',
            'message' => 'Address Selected',
        ]);
    }

    /**
     * Remove user address
     */
    public function rem_user_address(Request $request): JsonResponse
    {
        $address_id = $request->address_id;

        $address = Address::find($address_id);

        if (! $address) {
            return response()->json([
                'status' => '0',
                'message' => 'Address not found',
            ]);
        }

        // Check if address is used in any orders
        $hasOrders = Orders::where('address_id', $address_id)->exists();

        if ($hasOrders) {
            // Soft delete by setting select_status to 2
            $address->update(['select_status' => 2]);
        } else {
            // Hard delete if no orders exist
            $address->delete();
        }

        return response()->json([
            'status' => '1',
            'message' => 'Address Removed',
        ]);
    }

    /**
     * Edit address
     */
    public function edit_add(Request $request): JsonResponse
    {
        $address_id = $request->address_id;
        $user_id = $request->user_id;

        $address = Address::find($address_id);

        if (! $address) {
            return response()->json([
                'status' => '0',
                'message' => 'Address not found',
            ]);
        }

        // Reset all addresses to unselected
        Address::where('user_id', $user_id)->update(['select_status' => 0]);

        // Get city and society IDs
        $city = Cities::where('city_name', $request->city_name)->first();
        $society = Town::where('society_name', $request->society_name)->first();

        if (! $city || ! $society) {
            return response()->json([
                'status' => '0',
                'message' => 'Invalid city or society',
            ]);
        }

        $updateData = [
            'receiver_name' => $request->receiver_name,
            'receiver_phone' => $request->receiver_phone,
            'city' => $request->city_name,
            'society' => $request->society_name,
            'city_id' => $city->city_id,
            'society_id' => $society->society_id,
            'house_no' => $request->house_no,
            'landmark' => $request->landmark,
            'state' => $request->state,
            'pincode' => $request->pin,
            'select_status' => 1,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'type' => $request->type,
        ];

        $address->update($updateData);

        return response()->json([
            'status' => '1',
            'message' => 'Address Saved',
        ]);
    }

    /**
     * Show all addresses grouped by type
     */
    public function show_all_address(Request $request): JsonResponse
    {
        $user_id = $request->user_id;

        $addressTypes = Address::active()
            ->where('user_id', $user_id)
            ->select('type', 'address_id')
            ->get()
            ->unique('type');

        if ($addressTypes->count() === 0) {
            return response()->json([
                ['data' => 'No Address Found'],
            ]);
        }

        // Check if any address is selected, if not, select the first one
        $hasSelectedAddress = Address::where('user_id', $user_id)
            ->selected()
            ->exists();

        if (! $hasSelectedAddress) {
            Address::where('user_id', $user_id)
                ->active()
                ->first()
                ?->update(['select_status' => 1]);
        }

        $result = [];

        foreach ($addressTypes as $type) {
            $addresses = Address::active()
                ->where('user_id', $user_id)
                ->where('type', $type->type)
                ->get();

            if ($addresses->count() > 0) {
                $result[] = [
                    'type' => $type->type,
                    'data' => $addresses,
                ];
            } else {
                $result[] = ['data' => 'No Address Found'];
            }
        }

        return response()->json($result);
    }

    /**
     * Get societies for store service area
     */
    public function societyforadd(Request $request): JsonResponse
    {
        $store_id = $request->store_id;

        $societies = ServiceArea::where('store_id', $store_id)
            ->select('society_name', 'society_id')
            ->get();

        if ($societies->count() > 0) {
            return response()->json([
                'status' => '1',
                'message' => 'Society list',
                'data' => $societies,
            ]);
        }

        return response()->json([
            'status' => '0',
            'message' => 'Society not found',
        ]);
    }
}
