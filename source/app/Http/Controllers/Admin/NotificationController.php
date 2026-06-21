<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriverNotification;
use App\Models\StoreNotification;
use App\Models\UserNotification;
use App\Setting;
use App\Traits\FirebaseCloudMessaging;
use App\Traits\ImageStoragePicker;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class NotificationController extends Controller
{
    use FirebaseCloudMessaging;
    use ImageStoragePicker;

    public function usernotlist(Request $request)
    {
        $title = 'Users Notifications';
        $admin_email = Auth::guard('admin')->user()->email;

        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $notification = DB::table('user_notification')
            ->join('users', 'user_notification.user_id', '=', 'users.id')
            ->select('users.name', 'user_notification.*')
            ->paginate(20);
        $url_aws = $this->getImageStorage();

        return view('admin.notifications.userlist', compact('title', 'admin', 'logo', 'admin', 'notification', 'url_aws'));
    }

    public function delete_all_user(Request $request)
    {
        $notification = DB::table('user_notification')
            ->delete();
        if ($notification) {
            return redirect()->back()->withSuccess(trans('keywords.Deleted Successfully'));
        } else {
            return redirect()->back()->withErrors(trans('keywords.Something Wents Wrong'));
        }
    }

    public function delete_read_user(Request $request)
    {
        $notification = DB::table('user_notification')
            ->where('read_by_user', 1)
            ->delete();
        if ($notification) {
            return redirect()->back()->withSuccess(trans('keywords.Deleted Successfully'));
        } else {
            return redirect()->back()->withErrors(trans('keywords.Something Wents Wrong'));
        }
    }

    public function delete_all_store(Request $request)
    {
        $notification = DB::table('store_notification')
            ->delete();
        if ($notification) {
            return redirect()->back()->withSuccess(trans('keywords.Deleted Successfully'));
        } else {
            return redirect()->back()->withErrors(trans('keywords.Something Wents Wrong'));
        }
    }

    public function delete_all_driver(Request $request)
    {
        $notification = DB::table('driver_notification')
            ->delete();
        if ($notification) {
            return redirect()->back()->withSuccess(trans('keywords.Deleted Successfully'));
        } else {
            return redirect()->back()->withErrors(trans('keywords.Something Wents Wrong'));
        }
    }

    public function storenotlist(Request $request)
    {
        $title = 'Stores Notifications';
        $admin_email = Auth::guard('admin')->user()->email;

        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $notification = DB::table('store_notification')
            ->join('store', 'store_notification.store_id', '=', 'store.id')
            ->select('store.store_name', 'store_notification.*')
            ->paginate(20);
        $url_aws = $this->getImageStorage();

        return view('admin.notifications.storelist', compact('title', 'admin', 'logo', 'admin', 'notification', 'url_aws'));
    }

    public function drivernotlist(Request $request)
    {
        $title = 'Drivers Notifications';
        $admin_email = Auth::guard('admin')->user()->email;

        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $notification = DB::table('driver_notification')
            ->join('delivery_boy', 'driver_notification.dboy_id', '=', 'delivery_boy.dboy_id')
            ->select('delivery_boy.boy_name', 'driver_notification.*')
            ->paginate(20);
        $url_aws = $this->getImageStorage();

        return view('admin.notifications.driverlist', compact('title', 'admin', 'logo', 'admin', 'notification', 'url_aws'));
    }

    public function adminNotificationuser(Request $request)
    {
        $title = 'To Users';
        $admin_email = Auth::guard('admin')->user()->email;

        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $url_aws = $this->getImageStorage();

        $users = DB::table('users')
            ->join('city', 'users.user_city', '=', 'city.city_id')
            ->join('society', 'users.user_area', '=', 'society.society_id')
            ->join('store', 'city.city_name', '=', 'store.city')
            ->select('users.name', 'users.id', 'city.city_name', 'society.society_name')
            ->groupBy('users.name', 'users.id', 'city.city_name', 'society.society_name')
            ->get();

        return view('admin.settings.usernotification', compact('title', 'admin', 'logo', 'admin', 'users', 'url_aws'));
    }

    public function userNotificationSend(Request $request)
    {
        if (Setting::valActDeMode()) {
            return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        }
        $this->validate(
            $request,
            [
                'title' => 'required',
                'text' => 'required',
                'image' => 'mimes:jpeg,png,jpg|max:1000',
            ],
            [
                'title.required' => 'Enter notification title.',
                'text.required' => 'Enter notification text.',
            ]
        );
        $admin_email = Auth::guard('admin')->user()->email;

        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $user = $request->user;
        if ($user == null) {
            return redirect()->back()->withErrors(trans('keywords.Please Select any User(s)'));
        }
        $countuser = count($user);

        $date = date('d-m-Y');
        $url_aws = $this->getImageStorage();
        if ($request->hasFile('image')) {
            $image = $request->image;
            $fileName = $image->getClientOriginalName();
            $fileName = str_replace(' ', '-', $fileName);

            $this->getImageStorage();

            if ($this->storage_space != 'same_server') {
                $image_name = $image->getClientOriginalName();
                $image = $request->file('image');
                $filePath = '/notification/'.$image_name;
                Storage::disk($this->storage_space)->put($filePath, fopen($request->file('image'), 'r+'), 'public');
                $notify_image = $url_aws.$filePath;
            } else {

                $image->move('images/notification/'.$date.'/', $fileName);
                $filePath = '/images/notification/'.$date.'/'.$fileName;
                $notify_image = $url_aws.$filePath;

            }
        } else {
            $notify_image = 'N/A';
            $filePath = 'N/A';
        }

        $notification_title = $request->title;
        $notification_text = $request->text;

        if ($countuser >= 600) {
            $userin = DB::table('users')
                ->join('city', 'users.user_city', '=', 'city.city_id')
                ->join('society', 'users.user_area', '=', 'society.society_id')
                ->join('store', 'city.city_name', '=', 'store.city')
                ->select('users.device_id', 'users.name', 'users.id')
                ->get();
        } else {
            $userin = DB::table('users')->select('device_id', 'name', 'id')
                ->WhereIn('id', $user)
                ->get();
        }

        foreach ($userin as $us) {
            $get_device_id[] = $us;
        }
        $loop = count(array_chunk($get_device_id, 600));  // count array chunk 1000
        $arrayChunk = array_chunk($get_device_id, 600);   // devide array in 1000 chunk
        $notifications = [];
        $body = $notification_text;

        for ($i = 0; $i < $loop; $i++) {
            foreach ($arrayChunk[$i] as $all_device_id) {
                UserNotification::create([
                    'user_id' => $all_device_id->id,
                    'noti_title' => $notification_title,
                    'image' => $filePath,
                    'noti_message' => $notification_text,
                ]);

                $notifications[] = [
                    'message' => [
                        'token' => $all_device_id->device_id,
                        'notification' => [
                            'body' => $body,
                            'title' => $notification_title,
                            'image' => $notify_image,
                        ],
                    ],
                ];
            }

            $this->sendPushNotificationsInBulk($notifications);
            unset($notifications); // unset the array value

        }

        return redirect()->back()->withSuccess(trans('keywords.Notification Sent to user Successfully'));
    }

    public function adminNotification(Request $request)
    {
        $title = 'To Stores';
        $admin_email = Auth::guard('admin')->user()->email;

        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $store = DB::table('store')
            ->get();
        $url_aws = $this->getImageStorage();

        return view('admin.settings.notification', compact('title', 'admin', 'logo', 'admin', 'store', 'url_aws'));
    }

    public function Notification_to_store_Send(Request $request)
    {
        if (Setting::valActDeMode()) {
            return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        }
        $this->validate(
            $request,
            [
                'notification_title' => 'required',
                'notification_text' => 'required',
                'notify_image' => 'mimes:jpeg,png,jpg|max:400',
            ],
            [
                'notification_title.required' => 'Enter notification title.',
                'notification_text.required' => 'Enter notification text.',
            ]
        );

        $notification_title = $request->notification_title;
        $notification_text = $request->notification_text;
        $st = $request->st;
        if ($st == null) {
            return redirect()->back()->withErrors(trans('keywords.Please Select any Store(s)'));
        }
        $countstore = count($st);
        $date = date('d-m-Y');
        $url_aws = $this->getImageStorage();

        if ($request->hasFile('notify_image')) {
            $image = $request->notify_image;
            $fileName = $image->getClientOriginalName();
            $fileName = str_replace(' ', '-', $fileName);

            $this->getImageStorage();

            if ($this->storage_space != 'same_server') {
                $image_name = $image->getClientOriginalName();
                $image = $request->file('notify_image');
                $filePath = '/notification/'.$image_name;
                Storage::disk($this->storage_space)->put($filePath, fopen($request->file('notify_image'), 'r+'), 'public');
                $notify_image = $url_aws.$filePath;
            } else {

                $image->move('images/notification/'.$date.'/', $fileName);
                $filePath = '/images/notification/'.$date.'/'.$fileName;
                $notify_image = $url_aws.$filePath;

            }
        } else {
            $notify_image = 'N/A';
            $filePath = null;
        }

        $created_at = Carbon::now();

        $getFcm = DB::table('fcm')
            ->select('store_server_key')
            ->where('id', '1')
            ->first();

        $getFcmKey = $getFcm->store_server_key;

        for ($i = 0; $i <= ($countstore - 1); $i++) {

            $getDevice = DB::table('store')
                ->select('device_id', 'store_name')
                ->where('id', $st[$i])
                ->first();

            $store_name = $getDevice->store_name;
            $token = $getDevice->device_id;

            $notification = [
                'title' => 'Hey '.$store_name.', '.$notification_title,
                'body' => $notification_text,
                'image' => $notify_image,
                'sound' => true,
            ];

            $extraNotificationData = ['message' => $notification, 'image' => $notify_image];

            $fcmNotification = [
                'message' => [
                    'token' => $token, // single token
                    'notification' => $notification,
                    'data' => $extraNotificationData,
                ],
            ];

            $this->sendPushNotification($fcmNotification);

            StoreNotification::create([
                'store_id' => $st[$i],
                'not_title' => $notification_title,
                'not_message' => $notification_text,
                'image' => $filePath,
            ]);
        }

        return redirect()->back()->withSuccess(trans('keywords.Notification Sent to Store Successfully'));
    }

    public function adminNotificationdriver(Request $request)
    {
        $title = 'To Driver';
        $admin_email = Auth::guard('admin')->user()->email;
        $admin = DB::table('admin')
            ->leftJoin('roles', 'admin.role_id', '=', 'roles.role_id')
            ->where('admin.email', $admin_email)
            ->first();
        $logo = DB::table('tbl_web_setting')
            ->where('set_id', '1')
            ->first();
        $delivery = DB::table('delivery_boy')
            ->where('added_by', 'admin')
            ->get();
        $url_aws = $this->getImageStorage();

        return view('admin.settings.notification_to_driver', compact('title', 'admin', 'logo', 'admin', 'delivery', 'url_aws'));
    }

    public function Notification_to_driver_Send(Request $request)
    {
        if (Setting::valActDeMode()) {
            return redirect()->back()->withErrors(trans('keywords.Active_Demo_Mode'));
        }
        $this->validate(
            $request,
            [
                'notification_title' => 'required',
                'notification_text' => 'required',
                'notify_image' => 'mimes:jpeg,png,jpg|max:400',
            ],
            [
                'notification_title.required' => 'Enter notification title.',
                'notification_text.required' => 'Enter notification text.',
            ]
        );

        $notification_title = $request->notification_title;
        $notification_text = $request->notification_text;
        $st = $request->st;
        if ($st == null) {
            return redirect()->back()->withErrors(trans('keywords.Please Select any Driver(s)'));
        }
        $countstore = count($st);
        $date = date('d-m-Y');
        $url_aws = $this->getImageStorage();
        if ($request->hasFile('notify_image')) {
            $image = $request->notify_image;
            $fileName = $image->getClientOriginalName();
            $fileName = str_replace(' ', '-', $fileName);

            $this->getImageStorage();

            if ($this->storage_space != 'same_server') {
                $image_name = $image->getClientOriginalName();
                $image = $request->file('notify_image');
                $filePath = '/notification/'.$image_name;
                Storage::disk($this->storage_space)->put($filePath, fopen($request->file('notify_image'), 'r+'), 'public');
                $notify_image = $url_aws.$filePath;
            } else {

                $image->move('images/notification/'.$date.'/', $fileName);
                $filePath = '/images/notification/'.$date.'/'.$fileName;
                $notify_image = $url_aws.$filePath;

            }
        } else {
            $notify_image = 'N/A';
            $filePath = null;
        }

        for ($i = 0; $i <= ($countstore - 1); $i++) {

            $getDevice = DB::table('delivery_boy')
                ->select('device_id', 'boy_name')
                ->where('dboy_id', $st[$i])
                ->first();

            $store_name = $getDevice->boy_name;
            $token = $getDevice->device_id;

            $notification = [
                'title' => 'Hey '.$store_name.', '.$notification_title,
                'body' => $notification_text,
                'image' => $notify_image,
                'sound' => true,
            ];

            $extraNotificationData = ['message' => $notification, 'image' => $notify_image];

            $fcmNotification = [
                'message' => [
                    'token' => $token, // single token
                    'notification' => $notification,
                    'data' => $extraNotificationData,
                ],
            ];

            $this->sendPushNotification($fcmNotification);

            DriverNotification::create([
                'dboy_id' => $st[$i],
                'not_title' => $notification_title,
                'not_message' => $notification_text,
                'image' => $filePath,
            ]);
        }

        return redirect()->back()->withSuccess(trans('keywords.Notification Sent to Driver Successfully'));
    }
}
