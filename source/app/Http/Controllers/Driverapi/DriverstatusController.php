<?php

namespace App\Http\Controllers\Driverapi;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;

class DriverstatusController extends Controller
{
    public function status(Request $request)
    {
        $dboy_id = $request->dboy_id;
        $status = $request->status;
        $lat = $request->lat;
        $lng = $request->lng;

        // Normalize status to 0 or 1
        $normalizedStatus = $status ? 1 : 0;

        $updateData = ['status' => $normalizedStatus];

        // Add coordinates if provided
        if ($lat !== null) {
            $updateData['lat'] = $lat;
        }
        if ($lng !== null) {
            $updateData['lng'] = $lng;
        }

        $update = DB::table('delivery_boy')
            ->where('dboy_id', $dboy_id)
            ->update($updateData);

        if ($update) {
            $message = ['status' => '1', 'message' => 'Status Updated'];

            return $message;
        } else {
            $message = ['status' => '0', 'message' => 'Driver not found'];

            return $message;
        }
    }

    public function get_status(Request $request)
    {
        $dboy_id = $request->dboy_id;

        $update = DB::table('delivery_boy')
            ->select('status')
            ->where('dboy_id', $dboy_id)
            ->first();

        $orders = DB::table('orders')
            ->where('dboy_id', $dboy_id)
            ->Where('order_status', '!=', null)
            ->where('order_status', '!=', 'Cancelled')
            ->where('payment_method', '!=', null)
            ->count();
        $completed = DB::table('orders')
            ->where('dboy_id', $dboy_id)
            ->where('order_status', 'Completed')
            ->where('payment_method', '!=', null)
            ->count();

        $pending = DB::table('orders')
            ->where('dboy_id', $dboy_id)
            ->where('order_status', 'Confirmed')
            ->where('payment_method', '!=', null)
            ->count();

        $driver_incentive = DB::table('driver_incentive')
            ->where('dboy_id', $dboy_id)
            ->first();

        if ($driver_incentive) {
            $total_incentive = $driver_incentive->earned_till_now;
            $received_incentive = $driver_incentive->paid_till_now;
            $remaining_incentive = $driver_incentive->remaining;
        } else {
            $total_incentive = 0;
            $received_incentive = 0;
            $remaining_incentive = 0;
        }

        if ($update) {
            $driver = DB::table('delivery_boy')
                ->where('dboy_id', $dboy_id)
                ->first();

            $message = [
                'status' => '1',
                'message' => 'Driver Status',
                'data' => [
                    'dboy_id' => $driver->dboy_id,
                    'boy_name' => $driver->boy_name,
                    'status' => $update->status,
                    'lat' => $driver->lat,
                    'lng' => $driver->lng,
                ],
                'online_status' => $update->status,
                'total_orders' => $orders,
                'total_incentive' => $total_incentive,
                'received_incentive' => $received_incentive,
                'remaining_incentive' => $remaining_incentive,
                'pending_orders' => $pending,
                'completed_orders' => $completed,
            ];

            return $message;
        } else {
            $message = ['status' => '0', 'message' => 'Driver not found'];

            return $message;
        }
    }

    public function latlngupdate(Request $request)
    {
        $dboy_id = $request->dboy_id;
        $lat = $request->lat;
        $lng = $request->lng;
        $update = DB::table('delivery_boy')
            ->where('dboy_id', $dboy_id)
            ->update(['current_lat' => $lat,
                'current_lng' => $lng]);

        if ($update) {
            $message = ['status' => '1', 'message' => 'Lat Lng Updated'];

            return $message;
        } else {
            $message = ['status' => '0', 'message' => 'Nothing happened'];

            return $message;
        }

    }
}
