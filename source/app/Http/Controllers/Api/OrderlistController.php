<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ImageStoragePicker;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderlistController extends Controller
{
    use ImageStoragePicker;

    public function orderlist(Request $request)
    {
        $user_id = $request->user_id;
        $address_id = $request->address_id;
        $store_id = $request->store_id;
        $date = date('Y-m-d');

        // The app posts the photo as `orderlist`, but older builds send it as
        // `image`. Resolve either, and bail out with a normal API payload when
        // neither is present instead of fataling on a null upload.
        $image = $request->file('orderlist') ?: $request->file('image');

        if (! $image || ! $image->isValid()) {
            return ['status' => '0', 'message' => 'Please attach an order list photo'];
        }

        // Reject the duplicate before touching storage, otherwise every retry
        // leaves an orphaned file that no row ever references.
        $check = DB::table('order_by_photo')
            ->where('user_id', $user_id)
            ->where('store_id', $store_id)
            ->where('processed', 0)
            ->get();

        if (count($check) == 0) {
            $fileName = str_replace(' ', '-', $image->getClientOriginalName());

            $this->getImageStorage();

            if ($this->storage_space != 'same_server') {
                $filePath = '/images/order/'.$date.'/'.uniqid().'-'.$fileName;
                Storage::disk($this->storage_space)->put($filePath, fopen($image->getRealPath(), 'r+'));
            } else {

                $image->move('images/order/'.$date.'/', $fileName);
                $filePath = '/images/order/'.$date.'/'.$fileName;

            }

            $insert = DB::table('order_by_photo')
                ->insertgetid([
                    'user_id' => $user_id,
                    'list_photo' => $filePath,
                    'address_id' => $address_id,
                    'store_id' => $store_id,
                    'processed' => 0,
                ]);

            if ($insert) {
                $message = ['status' => '1', 'message' => 'Order List Submitted! you will get an sms and notification once it will processed'];

                return $message;
            } else {
                $message = ['status' => '0', 'message' => 'Please try again later'];

                return $message;
            }
        } else {
            $message = ['status' => '2', 'message' => 'You already submitted an Order list please wait till the older ones Confirmation.'];

            return $message;
        }

    }

    public function order_show_address(Request $request)
    {
        $user_id = $request->user_id;
        $lat = $request->lat;
        $lng = $request->lng;
        $nearbystore = DB::table('store')
            ->select('del_range', 'store_id', 'lat', 'lng', DB::raw('6371 * acos(cos(radians('.$lat.'))
                    * cos(radians(store.lat))
                    * cos(radians(store.lng) - radians('.$lng.'))
                    + sin(radians('.$lat.'))
                    * sin(radians(store.lat))) AS distance'))
            ->orderBy('distance')
            ->first();

        if ($nearbystore) {
            $store_id = $nearbystore->store_id;
            if ($nearbystore->del_range >= $nearbystore->distance) {

                $store = $nearbystore;

                $address = DB::table('address')
                    ->where('user_id', $user_id)
                    ->where('select_status', '!=', 2)
                    ->select('address.*', DB::raw('6371 * acos(cos(radians('.$store->lat.'))
                    * cos(radians(address.lat))
                    * cos(radians(address.lng) - radians('.$store->lng.'))
                    + sin(radians('.$store->lat.'))
                    * sin(radians(address.lat))) AS distancee'))
                    ->Having('distancee', '<=', $store->del_range)
                    ->orderBy('distancee')
                    ->get();

                if (count($address) > 0) {
                    foreach ($address as $addresses) {
                        $address_id[] = $addresses->address_id;
                    }
                    $check = DB::table('address')
                        ->WhereIn('address_id', $address_id)
                        ->where('select_status', 1)
                        ->get();
                    if (count($check) == 0) {
                        $selected = DB::table('address')
                            ->where('user_id', $user_id)
                            ->where('select_status', 1)
                            ->update(['select_status' => 0]);
                    }
                    $message = ['status' => '1', 'message' => 'Address list', 'data' => $address];

                    return $message;
                } else {
                    $message = ['status' => '0', 'message' => 'Address not found! Add Address', 'data' => []];

                    return $message;
                }
            } else {
                $message = ['status' => '0', 'message' => 'We are not delivering in your area', 'data' => []];

                return $message;
            }
        } else {
            $message = ['status' => '0', 'message' => 'We are not delivering in your area', 'data' => []];

            return $message;
        }
    }
}
