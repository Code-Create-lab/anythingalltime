<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Orders as Order;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    // This function will need extra argument if given coupon applies only for first order coupons.

    public function apply_coupon(Request $request)
    {
        $cart_id = $request->cart_id;
        $coupon_code = $request->coupon_code;
        $user_id = $request->user_id;   // This is a new parameter to be requested to validate when a coupon is for first orders.
        $order = new Order;
        $coupon = new Coupon;

        $coupon_info = $coupon->couponIsValid($coupon_code);

        if (is_bool($coupon_info)) {
            return ['status' => '0', 'message' => 'Coupon not valid. Contact store for further assistance.'];
        }  // If there is no valid coupon within the request, return the corresponding message

        if (is_null($cart_id) || ! isset($cart_id)) {
            return ['status' => '0', 'message' => 'Cart Id not found.'];
        }  // If there is no cart id within the request, return the corresponding message

        if ($coupon_info->typecoupon == 'forder') {
            // We must check if user has ordered before in the ecosystem. If orders are found, fail gracefully
            if (is_null($user_id) || ! isset($user_id)) {
                return ['status' => '0', 'message' => 'User not provided. Required for validating coupon.'];
            }
            $user_has_ordered = $order->getUserOrders($user_id, true);
            if ($user_has_ordered) {
                return ['status' => '0', 'message' => 'Invalid coupon. Only applicable for a first order. Contact store for further assistance.'];
            }  // Coupon can not be applied if cust has ordered already, return the corresponding message
            $coupon_info = ['id' => $coupon_info['coupon_id'], 'min_cart' => $coupon_info['cart_value'], 'type_discount' => $coupon_info['type'], 'amount' => $coupon_info['amount'], 'max_discount' => $coupon_info['max_discount'], 'max_uses' => $coupon_info['uses_restriction'], 'store_id' => $coupon_info['store_id']];
            $order_updated = $order->couponApply2Cart($cart_id, $coupon_info, $user_id);
        } else {
            // We retrieve items in the order by providing the cart id
            $coupon_info = ['id' => $coupon_info['coupon_id'], 'min_cart' => $coupon_info['cart_value'], 'type_discount' => $coupon_info['type'], 'amount' => $coupon_info['amount'], 'max_discount' => $coupon_info['max_discount'], 'max_uses' => $coupon_info['uses_restriction'], 'store_id' => $coupon_info['store_id']];
            $order_updated = $order->couponApply2Cart($cart_id, $coupon_info);
        }

        if (is_object($order_updated)) {
            return ['status' => '1', 'message' => 'Coupon Applied Successfully', 'data' => $order_updated];
        } else {
            if ($order_updated != -1) {
                $message = ($order_updated == 1) ? 'Coupon uses was exceeded for this user. Await more promotions or contact store for further assistance.' : 'Coupon was not applied! Try again later';

                return ['status' => '0', 'message' => $message, 'data' => $order->getOrderByCart($cart_id, true)];
            } else {
                return ['status' => '0', 'message' => 'Parameters given are inconsistant with order. Coupon can not be applied', 'data' => $request];
            }
        }
    }

    public function coupon_list_old(Request $request)
    {
        $currentdate = Carbon::now();
        $cart_id = $request->cart_id;
        $store_id = $request->store_id;
        $check = DB::table('orders')
            ->where('cart_id', $cart_id)
            ->first();
        if ($check) {
            $p = $check->total_price;
            $coupon = DB::table('coupon')
                ->where('store_id', $check->store_id)
                ->where('cart_value', '<=', $p)
                ->where('start_date', '<=', $currentdate)
                ->where('end_date', '>=', $currentdate)
                ->get();
        } else {
            $coupon = DB::table('coupon')
                ->where('store_id', $store_id)
                ->where('start_date', '<=', $currentdate)
                ->where('end_date', '>=', $currentdate)
                ->get();
        }

        if (count($coupon) > 0) {
            $couponss = [];

            foreach ($coupon as $coupons) {
                $check2 = 0;
                if ($check && isset($check->user_id)) {
                    $check2 = DB::table('orders')
                        ->where('coupon_id', $coupons->coupon_id)
                        ->where('user_id', $check->user_id)
                        ->where('order_status', '!=', 'Cancelled')
                        ->count();
                }
                $coupons->user_uses = $check2;

                $couponss[] = $coupons;

            }

            $message = ['status' => '1', 'message' => 'Coupon List', 'data' => $couponss];

            return $message;
        } else {
            $message = ['status' => '0', 'message' => 'Coupon not Found'];

            return $message;
        }

    }

    public function genCouponRndCode(Request $request)
    {
        $rand_length = mt_rand(5, 11);
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $coupon = '';

        $retflag = 0;
        $max_attempts = 100; // Prevent infinite loops
        $attempts = 0;

        while ($retflag == 0 && $attempts < $max_attempts) {
            $coupon = ''; // Reset coupon for each attempt
            for ($i = 0; $i < $rand_length; $i++) {
                $next_char = $chars[mt_rand(0, strlen($chars) - 1)];
                if ($i > 1) {
                    if ($next_char == $coupon[$i - 1]) {
                        $next_char = $chars[mt_rand(0, strlen($chars) - 1)];
                    }

                }
                $coupon .= $next_char;
            }

            $coupon_rec = DB::table('coupon')
                ->where('coupon_code', $coupon)
                ->first();

            if (is_null($coupon_rec)) {
                $retflag = 1;
            }
            $attempts++;
        }

        return $coupon;
    }
}
