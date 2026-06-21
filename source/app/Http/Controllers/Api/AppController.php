<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\AppLink;
use App\Models\CallbackRequest;
use App\Models\CountryCode;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\DeliveryBoy;
use App\Models\FCM;
use App\Models\Firebase;
use App\Models\FirebaseISO;
use App\Models\FreeDeliveryCart;
use App\Models\MembershipBought;
use App\Models\MembershipPlan;
use App\Models\MinimumMaximumOrderValue;
use App\Models\Orders;
use App\Models\PlanBuyHistory;
use App\Models\ReferralPoints;
use App\Models\SMSBy;
use App\Models\StoreBanner;
use App\Models\StoreOrders;
use App\Models\User;
use App\Models\WebSetting;
use App\Models\Wishlist;
use App\Traits\ImageStoragePicker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppController extends Controller
{
    use ImageStoragePicker;

    /**
     * Get app configuration and user data
     */
    public function app(Request $request): JsonResponse
    {
        $url_aws = $this->getImageStorage();
        $user_id = $request->user_id;
        $store_id = $request->store_id;

        // Get FCM server keys
        $server_keys = FCM::first();
        $user_server_key = $server_keys?->server_key ?? 'fhkjsdhskjdhksjdklsafasf';
        $store_server_key = $server_keys?->store_server_key ?? 'jadghhjasdjasgdhjagsdhjas';
        $driver_server_key = $server_keys?->driver_server_key ?? 'hjdgfjsdgjsfjsafjasf';

        // Get user wallet
        $user = User::find($user_id);
        $wallet = $user?->wallet ?? 0;

        // Get wishlist count
        $wishlistQuery = Wishlist::where('user_id', $user_id);
        if ($store_id) {
            $wishlistQuery->where('store_id', $store_id);
        }
        $wishlist_items = $wishlistQuery->count();

        // Get cart items count
        $cartSum = StoreOrders::where('store_approval', $user_id)
            ->where('order_cart_id', 'incart')
            ->selectRaw('SUM(price) as sum, COUNT(store_order_id) as count')
            ->first();

        $countp = ($cartSum && $user_id) ? $cartSum->count : 0;

        // Get app links
        $app_link = AppLink::first();
        $android = $app_link?->android_app_link;
        $ios = $app_link?->ios_app_link;

        // Get web settings
        $app = WebSetting::first();

        // Get Firebase settings
        $firebase_st = Firebase::first();
        $firebase_status = ($firebase_st?->status == '0') ? 'off' : 'on';

        // Get referral points
        $getScratchCard = ReferralPoints::first();
        $scratch_card_offers = $getScratchCard?->points;
        $min = $scratch_card_offers['min'] ?? 0;
        $max = $scratch_card_offers['max'] ?? 0;
        $refertext = 'Refer and Earn Wallet Amount Upto from '.$min.' to '.$max;

        // Get currency
        $currency = Currency::first();

        // Get country code
        $countrycode = CountryCode::first();
        $code = $countrycode?->country_code;

        // Get Firebase ISO
        $isocode = FirebaseISO::first();

        // Get SMS settings
        $check = SMSBy::first();
        $sms = ($check?->status == 1) ? 'on' : 'off';

        if ($app) {
            return response()->json([
                'status' => '1',
                'message' => 'App Name & Logo',
                'last_loc' => $app->last_loc,
                'phone_number_length' => $app->number_limit,
                'app_name' => $app->name,
                'app_logo' => $app->icon,
                'firebase' => $firebase_status,
                'country_code' => $code,
                'firebase_iso' => $isocode?->iso_code,
                'sms' => $sms,
                'currency_sign' => $currency?->currency_sign,
                'refertext' => $refertext,
                'total_items' => $countp,
                'android_app_link' => $android,
                'payment_currency' => $currency?->currency_name,
                'ios_app_link' => $ios,
                'image_url' => $url_aws,
                'wishlist_count' => $wishlist_items,
                'userwallet' => $wallet,
                'live_chat' => $app->live_chat,
                'user_server_key' => $user_server_key,
                'store_server_key' => $store_server_key,
                'driver_server_key' => $driver_server_key,
            ]);
        }

        return response()->json([
            'status' => '0',
            'message' => 'Data not found',
            'image_url' => $url_aws,
        ]);
    }

    /**
     * Get coupon list for a store
     */
    public function couponlist(Request $request): JsonResponse
    {
        $store_id = $request->store_id;
        $user_id = $request->user_id;

        $coupons = Coupon::where('store_id', $store_id)->get();

        if ($coupons->count() > 0) {
            foreach ($coupons as $coupon) {
                if ($user_id) {
                    $userUses = Orders::where('coupon_id', $coupon->coupon_id)
                        ->where('user_id', $user_id)
                        ->where('order_status', '!=', 'Cancelled')
                        ->count();
                    $coupon->user_uses = $userUses;
                } else {
                    $coupon->user_uses = 0;
                }
            }

            return response()->json([
                'status' => '1',
                'message' => 'Coupon list',
                'data' => $coupons,
            ]);
        }

        return response()->json([
            'status' => '0',
            'message' => 'Data not found',
        ]);
    }

    /**
     * Get delivery information
     */
    public function delivery_info(Request $request): JsonResponse
    {
        $del_fee = FreeDeliveryCart::first();

        if ($del_fee) {
            return response()->json([
                'status' => '1',
                'message' => 'Delivery fee and cart value',
                'data' => $del_fee,
            ]);
        }

        return response()->json([
            'status' => '0',
            'message' => 'Data not found',
        ]);
    }

    /**
     * Get store banners
     */
    public function storebanner(Request $request): JsonResponse
    {
        $store_id = $request->store_id;

        $banners = StoreBanner::with('category')
            ->where('store_id', $store_id)
            ->get();

        if ($banners->count() > 0) {
            return response()->json([
                'status' => '1',
                'message' => 'Banner List',
                'data' => $banners,
            ]);
        }

        return response()->json([
            'status' => '0',
            'message' => 'No Banner Found',
        ]);
    }

    /**
     * Handle callback request
     */
    public function call(Request $request): JsonResponse
    {
        $user_id = $request->user_id;
        $date = date('Y-m-d');
        $store_id = $request->store_id;

        $user = User::find($user_id);
        if (! $user) {
            return response()->json([
                'status' => '0',
                'message' => 'User not found',
            ]);
        }

        // Delete existing unprocessed callback request
        CallbackRequest::where('user_id', $user_id)
            ->where('processed', 0)
            ->delete();

        // Create new callback request
        $callbackData = [
            'user_id' => $user_id,
            'user_name' => $user->name,
            'user_phone' => $user->user_phone,
            'date' => $date,
            'store_id' => $store_id ?? 0,
            'processed' => 0,
        ];

        $callbackRequest = CallbackRequest::create($callbackData);

        if ($callbackRequest) {
            return response()->json([
                'status' => '1',
                'message' => 'Callback requested successfully',
            ]);
        }

        return response()->json([
            'status' => '0',
            'message' => 'Try again later',
        ]);
    }

    /**
     * Get minimum/maximum order value for a store
     */
    public function minmax(Request $request): JsonResponse
    {
        $store_id = $request->store_id;

        $minmax = MinimumMaximumOrderValue::where('store_id', $store_id)->first();

        if ($minmax) {
            return response()->json([
                'status' => '1',
                'message' => 'Min/Max Cart Value',
                'data' => $minmax,
            ]);
        }

        return response()->json([
            'status' => '0',
            'message' => 'Min/Max Cart Value not found',
        ]);
    }

    /**
     * Payment view
     */
    public function payment(Request $request)
    {
        return view('admin.payment');
    }

    /**
     * Success view
     */
    public function success(Request $request)
    {
        return view('admin.success');
    }

    /**
     * Failed view
     */
    public function failed(Request $request)
    {
        return view('admin.failed');
    }

    /**
     * Get membership plans
     */
    public function membership_plan(Request $request): JsonResponse
    {
        $membershipPlans = MembershipPlan::paginate(10);

        if ($membershipPlans->count() > 0) {
            return response()->json([
                'status' => '1',
                'message' => 'Membership plan',
                'data' => $membershipPlans->items(),
            ]);
        }

        return response()->json([
            'status' => '0',
            'message' => 'Data not found',
        ]);
    }

    /**
     * Get membership status for a user
     */
    public function membership_status(Request $request): JsonResponse
    {
        $user_id = $request->user_id;
        $currentDate = date('Y-m-d');

        $user = User::where('id', $user_id)
            ->whereDate('mem_plan_expiry', '>=', $currentDate)
            ->whereNotNull('mem_plan_expiry')
            ->first();

        if ($user) {
            $plan = MembershipPlan::find($user->membership);
            $running = [
                'membership_status' => $plan,
                'status' => 'running',
            ];

            return response()->json([
                'status' => '1',
                'message' => 'Membership plan details',
                'data' => $running,
            ]);
        }

        // Check for expired membership
        $expiredUser = User::where('id', $user_id)
            ->whereDate('mem_plan_expiry', '<', $currentDate)
            ->whereNotNull('mem_plan_expiry')
            ->first();

        if ($expiredUser) {
            $plan = MembershipPlan::find($expiredUser->membership);
            $expired = [
                'membership_status' => $plan,
                'status' => 'expired',
            ];

            return response()->json([
                'status' => '1',
                'message' => 'Membership plan details',
                'data' => $expired,
            ]);
        }

        return response()->json([
            'status' => '0',
            'message' => 'No plan bought yet',
        ]);
    }

    /**
     * Buy membership for a user
     */
    public function buymember(Request $request): JsonResponse
    {
        $user_id = $request->user_id;
        $plan_id = $request->plan_id;
        $recharge_method = $request->buy_status;
        $payment_gateway = $request->payment_gateway;
        $transaction_id = $request->transaction_id;

        $plan = MembershipPlan::find($plan_id);
        if (! $plan) {
            return response()->json([
                'status' => '0',
                'message' => 'Plan not found',
            ]);
        }

        $mem_price = $plan->price;
        $currentDate = date('Y-m-d');
        $days = $plan->days;

        // Check existing membership
        $existingMembership = MembershipBought::where('user_id', $user_id)->first();

        if ($existingMembership) {
            if (strtotime($existingMembership->mem_end_date) == strtotime($currentDate)) {
                $days = 1;
                $currentDate = date('Y-m-d', strtotime($currentDate.' + '.$days.' days'));
            }

            if (strtotime($existingMembership->mem_end_date) > strtotime($currentDate)) {
                return response()->json([
                    'status' => '5',
                    'message' => 'You have an ongoing membership you cannot buy another till its expiry',
                ]);
            }
        }

        $end = date('Y-m-d', strtotime($currentDate.' + '.$days.' days'));
        $user = User::find($user_id);

        if ($recharge_method == 'wallet') {
            $amount = $user->wallet;

            if ($amount >= $mem_price) {
                $final_amount = $amount - $mem_price;

                $user->update([
                    'wallet' => $final_amount,
                    'membership' => $plan_id,
                    'mem_plan_start' => $currentDate,
                    'mem_plan_expiry' => $end,
                ]);

                MembershipBought::create([
                    'user_id' => $user_id,
                    'mem_id' => $plan_id,
                    'mem_start_date' => $currentDate,
                    'mem_end_date' => $end,
                    'price' => $mem_price,
                    'buy_date' => $currentDate,
                    'paid_by' => $recharge_method,
                    'transaction_id' => $transaction_id,
                    'payment_gateway' => $payment_gateway,
                    'payment_status' => $recharge_method,
                ]);

                PlanBuyHistory::create([
                    'user_id' => $user_id,
                    'type' => 'Membership Bought',
                    'amount' => $mem_price,
                    'before_recharge' => $amount,
                    'after_recharge' => $final_amount,
                    'created_at' => $currentDate,
                ]);

                return response()->json([
                    'status' => '1',
                    'message' => 'Membership bought successfully.',
                ]);
            } else {
                return response()->json([
                    'status' => '2',
                    'message' => 'Your wallet balance is low! Please Recharge',
                ]);
            }
        } else {
            if ($recharge_method == 'success') {
                $user->update([
                    'membership' => $plan_id,
                    'mem_plan_start' => $currentDate,
                    'mem_plan_expiry' => $end,
                ]);

                MembershipBought::create([
                    'user_id' => $user_id,
                    'mem_id' => $plan_id,
                    'mem_start_date' => $currentDate,
                    'mem_end_date' => $end,
                    'price' => $mem_price,
                    'buy_date' => $currentDate,
                    'paid_by' => $recharge_method,
                    'transaction_id' => $transaction_id,
                    'payment_gateway' => $payment_gateway,
                    'payment_status' => $recharge_method,
                ]);

                return response()->json([
                    'status' => '1',
                    'message' => 'Membership bought successfully.',
                ]);
            } else {
                MembershipBought::create([
                    'user_id' => $user_id,
                    'mem_id' => $plan_id,
                    'mem_start_date' => $currentDate,
                    'mem_end_date' => $end,
                    'price' => $mem_price,
                    'buy_date' => $currentDate,
                    'paid' => 'failed',
                    'paid_by' => $recharge_method,
                    'transaction_id' => $transaction_id,
                    'payment_gateway' => $payment_gateway,
                    'payment_status' => $recharge_method,
                ]);

                return response()->json([
                    'status' => '3',
                    'message' => 'Payment failed! Try again later.',
                ]);
            }
        }
    }

    /**
     * Generate hash for payment
     */
    public function genhash(Request $request): JsonResponse
    {
        $merchant_secret = $request->merchant_secret;
        $ord_mercID = $request->ord_mercID;
        $ord_mercref = $request->ord_mercref;
        $amount = $request->amount;

        $allinone = $merchant_secret.$ord_mercID.$ord_mercref.$amount;
        $generate = hash('sha256', $allinone);

        return response()->json([
            'status' => '1',
            'message' => 'sha1 generated',
            'data' => $generate,
        ]);
    }

    /**
     * Delete all users (admin function)
     */
    public function delete_users(Request $request): JsonResponse
    {
        $deleted = User::query()->delete();

        if ($deleted) {
            // Delete related data
            Orders::query()->delete();
            StoreOrders::query()->delete();
            DB::table('cart_payments')->delete();
            DB::table('cart_rewards')->delete();
            DB::table('cart_status')->delete();
            DB::table('user_notification')->delete();
            DB::table('wallet_recharge_history')->delete();

            return response()->json([
                'status' => '1',
                'message' => 'Deleted',
            ]);
        }

        return response()->json([
            'status' => '0',
            'message' => 'No user found',
        ]);
    }

    /**
     * Track order details
     */
    public function trackorder(Request $request): JsonResponse
    {
        $cart_id = $request->cart_id;

        $order = Orders::with(['address', 'store', 'cartStatus', 'deliveryBoy'])
            ->where('cart_id', $cart_id)
            ->first();

        if ($order) {
            // Build delivery address
            $address = $order->address;
            $delivery_address = $address->house_no.','.$address->society.','.$address->city.','.$address->landmark.','.$address->state.','.$address->pincode;
            $order->delivery_address = $delivery_address;

            // Set order status flags
            $orderStatus = strtolower($order->order_status);

            if ($orderStatus == 'pending') {
                $order->pending = 'true';
                $order->confirm = 'false';
                $order->out_for_delivery = 'false';
                $order->completed = 'false';
                $order->cancelled = 'false';
                $order->estimated_time = null;
            } elseif ($orderStatus == 'confirmed') {
                $order->pending = 'true';
                $order->confirm = 'true';
                $order->out_for_delivery = 'false';
                $order->completed = 'false';
                $order->cancelled = 'false';
                $order->estimated_time = null;
            } elseif ($orderStatus == 'out_for_delivery') {
                // Calculate estimated time based on distance
                $deliveryBoy = DeliveryBoy::selectRaw('6371 * acos(cos(radians(?)) * cos(radians(current_lat)) * cos(radians(current_lng) - radians(?)) + sin(radians(?)) * sin(radians(current_lat))) AS distance', [
                    $address->lat, $address->lng, $address->lat,
                ])->first();

                $time = ($deliveryBoy->distance * 1000) / 40000;
                $est_time = $time / 60;
                $est_time = ($est_time <= 1) ? 1 : round($est_time, 0);

                $order->pending = 'true';
                $order->confirm = 'true';
                $order->out_for_delivery = 'true';
                $order->completed = 'false';
                $order->cancelled = 'false';
                $order->estimated_time = $est_time.' minutes';
            } elseif ($orderStatus == 'completed') {
                $order->pending = 'true';
                $order->confirm = 'true';
                $order->out_for_delivery = 'true';
                $order->completed = 'true';
                $order->cancelled = 'false';
                $order->estimated_time = null;
            } else {
                $order->pending = 'true';
                $order->confirm = 'false';
                $order->out_for_delivery = 'false';
                $order->completed = 'false';
                $order->cancelled = 'true';
                $order->estimated_time = null;
            }

            return response()->json([
                'status' => '1',
                'message' => 'Track order details',
                'data' => $order,
            ]);
        }

        return response()->json([
            'status' => '0',
            'message' => 'Order not found',
        ]);
    }
}
