<?php

namespace App\Http\Controllers\Driverapi;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;

class DriverNotificationController extends Controller
{
    public function notificationlist(Request $request)
    {
        $driver_id = $request->dboy_id;
        $notifyby = DB::table('driver_notification')
            ->where('dboy_id', $driver_id)
            ->orderBy('notification_date', 'desc')
            ->get();

        if (count($notifyby) > 0) {
            $message = ['status' => '1', 'message' => 'Notification List', 'data' => $notifyby];

            return $message;
        } else {
            $message = ['status' => '0', 'message' => 'No Notifications'];

            return $message;
        }
    }

    public function read_by_driver(Request $request)
    {
        $noti_id = $request->not_id ?? $request->notification_id;
        $notifyby = DB::table('driver_notification')
            ->where(function ($query) use ($noti_id) {
                $query->where('not_id', $noti_id)
                    ->orWhere('notification_id', $noti_id)
                    ->orWhere('id', $noti_id);
            })
            ->update(['seen' => 1, 'read_by_driver' => 1]);

        if ($notifyby) {
            $message = ['status' => '1', 'message' => 'Notification read'];

            return $message;
        } else {
            $message = ['status' => '0', 'message' => 'Notification not found', 'data' => []];

            return $message;
        }
    }

    public function all_as_read(Request $request)
    {
        $driver_id = $request->dboy_id;
        $notifyby = DB::table('driver_notification')
            ->where('dboy_id', $driver_id)
            ->update(['seen' => 1, 'read_by_driver' => 1]);

        if ($notifyby) {
            $message = ['status' => '1', 'message' => 'All notifications marked as read'];

            return $message;
        } else {
            $message = ['status' => '0', 'message' => 'Not Found', 'data' => []];

            return $message;
        }
    }

    public function delete_all(Request $request)
    {
        $driver_id = $request->dboy_id;
        $notifyby = DB::table('driver_notification')
            ->where('dboy_id', $driver_id)
            ->delete();

        if ($notifyby) {
            $message = ['status' => '1', 'message' => 'All notifications deleted'];

            return $message;
        } else {
            $message = ['status' => '0', 'message' => 'Not Found'];

            return $message;
        }
    }
}
