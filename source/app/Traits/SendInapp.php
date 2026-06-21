<?php

namespace App\Traits;

use App\Helpers\TokenType;
use App\Models\DriverNotification;
use App\Models\Store;
use App\Models\StoreNotification;
use App\Models\User;
use App\Models\UserNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait SendInapp
{
    use FirebaseCloudMessaging;

    private array $class_map = [
        TokenType::User->value => User::class,
        TokenType::Store->value => Store::class,
        TokenType::Driver->value => null,
    ];

    private function getToken(TokenType $tokenType, int $recordId): ?string
    {
        $class = $this->class_map[$tokenType->value];

        if ($class === null) {
            return null;
        }

        return $this->device_id($class, $recordId);
    }

    private function device_id(string $class, int $recordId): ?string
    {
        $record = $class::where('id', $recordId)
            ->select('device_id')
            ->first();

        return $record?->device_id;
    }

    private function prepareNotification(string $deviceToken, string $notification_title, string $notification_text): void
    {
        $notification = [
            'title' => $notification_title,
            'body' => $notification_text,
            'sound' => true,
        ];

        $extraNotificationData = ['message' => $notification];

        $fcmNotification = [
            'message' => [
                'token' => $deviceToken,
                'notification' => $notification,
                'data' => $extraNotificationData,
            ],
        ];

        $this->sendPushNotification($fcmNotification);
    }

    private function saveNotificationToDatabase(TokenType $tokenType, int $recordId, string $notification_title, string $notification_text): void
    {
        $dater = Carbon::now();
        switch ($tokenType->value) {
            case TokenType::User:
                UserNotification::create([
                    'user_id' => $recordId,
                    'noti_title' => $notification_title,
                    'noti_message' => $notification_text,
                    'created_at' => $dater,
                ]);
                break;
            case TokenType::Store:
                StoreNotification::create([
                    'store_id' => $recordId,
                    'noti_title' => $notification_title,
                    'noti_message' => $notification_text,
                    'created_at' => $dater,
                ]);
                break;
            case TokenType::Driver:
                DriverNotification::create([
                    'dboy_id' => $recordId,
                    'not_title' => $notification_title,
                    'not_message' => $notification_text,
                    'created_at' => $dater,
                ]);
                break;
        }
    }

    // //for user////
    // /////Order Placed///////
    public function codorderplacedinapp($cart_id, $prod_name, $price2, $delivery_date, $time_slot, $user_email, $user_name, $user_id): void
    {
        $notification_title = 'Hey '.$user_name.', Your Order is Placed';
        $notification_text = 'Order Successfully Placed: Your order id #'.$cart_id.' contains of '.$prod_name.' of price rs '.$price2.' is placed Successfully.You can expect your item(s) will be delivered on '.$delivery_date.' between '.$time_slot.'.';

        $deviceToken = $this->getToken(TokenType::User, $user_id);

        $this->prepareNotification($deviceToken, $notification_title, $notification_text);
        $this->saveNotificationToDatabase(TokenType::User, $user_id, $notification_title, $notification_text);
    }

    // ////////confirm Order /////////
    public function orderconfirmedinapp($cart_id, $user_phone, $orr): void
    {
        $user = DB::table('users')
            ->where('user_phone', $user_phone)
            ->first();
        $user_name = $user->name;
        $notification_title = 'Hey '.$user_name.', Your Order is Confirmed';
        $notification_text = 'Your Order is confirmed: Your order id #'.$cart_id.' is confirmed by the store.You can expect your item(s) will be delivered on '.$orr->delivery_date.' ('.$orr->time_slot.').';

        $deviceToken = $user->device_id;
        $this->prepareNotification($deviceToken, $notification_title, $notification_text);
        $this->saveNotificationToDatabase(TokenType::User, $user->id, $notification_title, $notification_text);
    }

    // /////Out For Delivery(Prepaid) /////////
    public function deloutinappcard($cart_id, $prod_name, $price2, $currency, $ord, $user_phone, $user_id, $store_n): void
    {
        $us = DB::table('users')
            ->where('id', $user_id)
            ->first();

        $user_name = $us->name;
        $notification_title = 'Hey '.$user_name.', Your Order is Out For Delivery';
        $notification_text = 'Out For Delivery: Your order id #'.$cart_id.' contains of '.$prod_name.' of price '.($currency ? $currency->currency_sign : '$').' '.$price2.' is Out For Delivery.Get ready.';

        $deviceToken = $us->device_id;
        $this->prepareNotification($deviceToken, $notification_title, $notification_text);
        $this->saveNotificationToDatabase(TokenType::User, $us->id, $notification_title, $notification_text);
    }

    // /////Out For Delivery(COD) /////////
    public function deloutinapp($cart_id, $prod_name, $price2, $currency, $ord, $user_phone, $user_id, $store_n): void
    {
        $us = DB::table('users')
            ->where('id', $user_id)
            ->first();
        $user_name = $us->name;
        $notification_title = 'Hey '.$user_name.', Your Order is Out For Delivery';
        $notification_text = 'Out For Delivery: Your order id #'.$cart_id.' contains of '.$prod_name.' of price '.($currency ? $currency->currency_sign : '$').' '.$price2.' is Out For Delivery.Get ready with '.($currency ? $currency->currency_sign : '$').' '.$ord->rem_price.' cash.';

        $deviceToken = $us->device_id;
        $this->prepareNotification($deviceToken, $notification_title, $notification_text);
        $this->saveNotificationToDatabase(TokenType::User, $us->id, $notification_title, $notification_text);
    }

    // /////Delivery Complete////////////
    public function delcominapp($cart_id, $prod_name, $price2, $currency, $ord, $user_phone, $user_id): void
    {
        $us = DB::table('users')
            ->where('id', $user_id)
            ->first();
        $user_name = $us->name;
        $notification_title = 'Hey '.$user_name.', Your Order has been Delivered';
        $notification_text = 'Delivery Completed: Your order id #'.$cart_id.' contains of '.$prod_name.' of price '.($currency ? $currency->currency_sign : '$').' '.$price2.' is Delivered Successfully.';

        $deviceToken = $us->device_id;
        $this->prepareNotification($deviceToken, $notification_title, $notification_text);
        $this->saveNotificationToDatabase(TokenType::User, $us->id, $notification_title, $notification_text);
    }

    // //////////Order reject By Admin////////

    public function sendrejectnotification($cause, $user, $cart_id, $user_id): void
    {
        $notification_title = 'Sorry! we are cancelling your order';
        $notification_text = 'Hello '.$user->name.', We are cancelling your order ('.$cart_id.') due to following reason:  '.$cause;

        $this->prepareNotification($user->device_id, $notification_title, $notification_text);
        $this->saveNotificationToDatabase(TokenType::User, $user->id, $notification_title, $notification_text);
    }

    // //for store////
    // /////Order Placed///////
    public function codorderplacedinappstore($cart_id, $prod_name, $price2, $delivery_date, $time_slot, $user_email, $user_name, $store_n, $store_id): void
    {
        $notification_title = 'Hey '.$store_n.', You Got a New Order';
        $notification_text = 'You got an order cart id #'.$cart_id.' contains of '.$prod_name.' of price rs '.$price2.'. It will have to delivered on '.$delivery_date.' between '.$time_slot.'.';

        $deviceToken = $this->getToken(TokenType::Store, $store_id);

        $this->prepareNotification($deviceToken, $notification_title, $notification_text);
        $this->saveNotificationToDatabase(TokenType::Store, $store_id, $notification_title, $notification_text);
    }

    // ///Out For Delivery////////////////////

    public function deloutinappstore($cart_id, $prod_name, $price2, $currency, $ord, $user_phone, $user_id, $store_id, $store_n): void
    {
        $notification_title = 'Hey '.$store_n.', Order id #'.$cart_id.' is Out For Delivery.';
        $notification_text = 'Out For Delivery: Order id #'.$cart_id.' contains of '.$prod_name.' of price '.($currency ? $currency->currency_sign : '$').' '.$price2.' is Out For Delivery.';

        $deviceToken = $this->getToken(TokenType::Store, $store_id);

        $this->prepareNotification($deviceToken, $notification_title, $notification_text);
        $this->saveNotificationToDatabase(TokenType::Store, $store_id, $notification_title, $notification_text);
    }

    // ///Delivery Completed////////////////////

    public function delcominappstore($cart_id, $prod_name, $price2, $currency, $ord, $user_phone, $user_id, $store_id): void
    {
        $st = DB::table('store')
            ->where('id', $store_id)
            ->first();
        $store_n = $st->store_name;
        $notification_title = 'Hey '.$store_n.', Order id #'.$cart_id.' has been Delivered.';
        $notification_text = 'Delivery Completed: Order id #'.$cart_id.' contains of '.$prod_name.' of price '.($currency ? $currency->currency_sign : '$').' '.$price2.' has been delivered.';

        $deviceToken = $st->device_id;

        $this->prepareNotification($deviceToken, $notification_title, $notification_text);
        $this->saveNotificationToDatabase(TokenType::Store, $store_id, $notification_title, $notification_text);
    }

    // ///for driver////
    public function orderconfirmedinappdriver($getDDevice, $cart_id, $user_phone, $orr, $curr): void
    {
        $notification_title = 'Hey '.$getDDevice->boy_name.', You Got a New Order for Delivery on '.$orr->delivery_date;
        $notification_text = 'you got an order with cart id #'.$cart_id.' of price '.$curr->currency_sign.' '.$orr->total_price.'. It will have to delivered on '.$orr->delivery_date.' between '.$orr->time_slot.'.';

        $deviceToken = $getDDevice->device_id;

        $this->prepareNotification($deviceToken, $notification_title, $notification_text);
        $this->saveNotificationToDatabase(TokenType::Driver, $getDDevice->id, $notification_title, $notification_text);
    }

    public function sendnotification($user, $userin, $notification_title, $notification_text, $notify_image): void
    {
        $userin = DB::table('users')->select('device_id', 'name', 'id')
            ->WhereIn('id', $user)
            ->get();

        $dater = Carbon::now();

        // you can insert post data into db for record here

        foreach ($userin as $us) {
            $get_device_id[] = $us;
        }
        $loop = count(array_chunk($get_device_id, 400));  // count array chunk 1000
        $arrayChunk = array_chunk($get_device_id, 400);   // devide array in 1000 chunk
        $device_id = [];
        for ($i = 0; $i < $loop; $i++) {
            foreach ($arrayChunk[$i] as $all_device_id) {

                $device_id[] = $all_device_id->device_id;

                $insertNotification = DB::table('user_notification')
                    ->insert([
                        'user_id' => $all_device_id->id,
                        'noti_title' => $notification_title,
                        'image' => $notification_text,
                        'noti_message' => $notify_image,
                        'created_at' => $dater,

                    ]);
            }

            $body = $notification_text;
            foreach ($device_id as $deviceToken) {
                $json_data = [
                    'message' => [
                        'token' => $deviceToken,
                        'notification' => [
                            'body' => $body,
                            'title' => $notification_title,
                        ],
                    ],
                ];

                $this->sendPushNotification($json_data);
            }

            unset($device_id); // unset the array value
        }
    }
}
