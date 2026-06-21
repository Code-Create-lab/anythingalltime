<?php

namespace App\Http\Controllers\Storeapi;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function notificationlist(Request $request)
    {
        $store_id = $request->store_id;
        $limit = $request->limit ?? 20; // Default limit of 20
        $notifyby = DB::table('store_notification')
            ->where('store_id', $store_id)
            ->orderBy('notification_date', 'desc')
            ->limit($limit)
            ->get();

        if (count($notifyby) > 0) {
            $message = ['status' => '1', 'message' => 'Notification List', 'data' => $notifyby];

            return $message;
        } else {
            $message = ['status' => '0', 'message' => 'No Notifications'];

            return $message;
        }
    }

    public function read_by_store(Request $request)
    {
        $noti_id = $request->not_id ?? $request->notification_id;
        $notifyby = DB::table('store_notification')
            ->where(function ($query) use ($noti_id) {
                $query->where('not_id', $noti_id)
                    ->orWhere('notification_id', $noti_id)
                    ->orWhere('id', $noti_id);
            })
            ->update(['seen' => 1, 'read_by_store' => 1]);

        if ($notifyby) {
            $message = ['status' => '1', 'message' => 'Notification read'];

            return $message;
        } else {
            $message = ['status' => '0', 'message' => 'Notification not found'];

            return $message;
        }
    }

    public function all_as_read(Request $request)
    {
        $store_id = $request->store_id;
        $notifyby = DB::table('store_notification')
            ->where('store_id', $store_id)
            ->update(['seen' => 1, 'read_by_store' => 1]);

        if ($notifyby) {
            $message = ['status' => '1', 'message' => 'All notifications marked as read'];

            return $message;
        } else {
            $message = ['status' => '0', 'message' => 'No notifications found'];

            return $message;
        }
    }

    public function delete_all(Request $request)
    {
        $store_id = $request->store_id;
        $notifyby = DB::table('store_notification')
            ->where('store_id', $store_id)
            ->delete();

        if ($notifyby) {
            $message = ['status' => '1', 'message' => 'All notifications deleted'];

            return $message;
        } else {
            $message = ['status' => '0', 'message' => 'No notifications found'];

            return $message;
        }
    }
}
