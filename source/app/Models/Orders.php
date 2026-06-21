<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $primaryKey = 'order_id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'store_id',
        'address_id',
        'cart_id',
        'total_price',
        'price_without_delivery',
        'total_products_mrp',
        'payment_method',
        'paid_by_wallet',
        'rem_price',
        'avg_tax_per',
        'total_tax_price',
        'order_date',
        'delivery_date',
        'delivery_charge',
        'time_slot',
        'dboy_id',
        'order_status',
        'user_signature',
        'cancelling_reason',
        'coupon_id',
        'coupon_discount',
        'payment_status',
        'cancel_by_store',
        'dboy_incentive',
        'updated_at',
    ];

    protected $attributes = [
        'paid_by_wallet' => 0,
        'rem_price' => 0,
        'delivery_charge' => 0,
        'dboy_id' => 0,
        'order_status' => 'Pending',
        'coupon_id' => 0,
        'coupon_discount' => 0,
        'cancel_by_store' => 0,
    ];

    /**
     * If the last week's value is greater than zero, then return the difference between this week's
     * value and last week's value divided by last week's value divided by 100. Otherwise, return this
     * week's value.
     *
     * @param float last_week the number of orders from last week
     * @param float this_week the number of orders this week
     * @return The percentage change in orders from last week to this week.
     */
    public static function getOrdersIndex(float $last_week, float $this_week)
    {
        if ($last_week > 0) {
            $reference = $last_week / 100;

            return ($this_week - $last_week) / $reference;
        } else {
            return $this_week;
        }
    }

    public function getOrderByCart(string $cart_id, bool $only_exists = false)
    {
        if ($only_exists) {
            return Orders::where('cart_id', $cart_id)->first();
        } else {
            $result = Orders::where('cart_id', $cart_id)->get();

            return ($result->count() > 0) ? $result : false;
        }
    }

    /**
     * A function to fetch all orders for a specific User. It can return first result or all records related.
     *
     * @param int user_id The user id of the user you want to get the orders for.
     * @param bool only_exists if true, it will return the first result, if false, it will return all
     * results.
     * @return An array with orders related for a specific user, if .
     */
    public function getUserOrders(int $user_id, bool $only_exists = false)
    {
        if ($only_exists) {
            return Orders::where('user_id', $user_id)->first();
        } else {
            $result = Orders::where('user_id', $user_id)->get();

            return ($result->count() > 0) ? $result : false;
        }
    }

    public function getUserOrderStore(int $user_id, int $store_id, bool $only_exists = false)
    {
        if ($only_exists) {

        } else {

        }
    }

    // This function will fetch Order info using a particular coupon code
    public function getOrdersUsingCoupon(string $cart_id, string $coupon_code, bool $only_exists = false) {}

    public function getOrderByStore(int $store_id, bool $only_exists = false)
    {
        if ($only_exists) {
            return Orders::where('store_id', $store_id)->first();
        } else {
            $result = Orders::where('store_id', $store_id)->get();

            return ($result->count() > 0) ? $result : false;
        }
    }

    public function getOrderByID(int $order_id)
    {
        return Orders::where('order_id', $order_id)->first();
    }

    /*
        - Check Min Cart Value
        - Coupon Max Uses
        - Use User_Id only when there are no previous orders. Otherwise, it will be checked by default
    */
    public function couponApply2Cart(string $cart_id, array $coupon_info, ?int $user_id = null)
    {
        if (! isset($coupon_info['id'], $coupon_info['store_id'])) {
            return -1;
        }
        $order = $this->getOrderByCart($cart_id, true);
        if ($order->order_status == 'Cancelled') {
            return -1;
        }
        if ($order->store_id !== $coupon_info['store_id']) {
            return -1;
        }   // If coupon doesn't belongs to store, fail

        // If User_id is set, it means there are no orders within the system for that user
        if ($user_id !== null) {
            if ($user_id !== $order->user_id) {
                return -1;
            }      // We need to double check cart belongs to user_id, otherwise fail
        } else {
            $user_id = $order->user_id; // First order condition met, user_id needs to be assigned directly from cart_id
            $user_orders_from_store = $this->getUserOrderStore($user_id, $coupon_info['store_id']);  // Get Orders from User to a particular store
            $max_uses_cnt = 0;
            // We must check if user_orders_from_store have applied this coupon and count results, if max_exceed return false
            foreach ($user_orders_from_store as $order) {
                if ($order->coupon_id == $coupon_info['id']) {
                    $max_uses_cnt++;
                }
            }
            if ($max_uses_cnt > $coupon_info['max_uses']) {
                return 1;
            }
        }
        if (isset($coupon_info['min_cart']) && ($coupon_info['min_cart'] > 0) && ($order->total_price < $coupon_info['min_cart'])) {
            return false;
        }
        // Create list of fields to update once coupon can be applied
        $total_price = $order->total_price;
        $per = ($coupon_info['type_discount'] == 'percent') ? ($total_price * $coupon_info['amount']) / 100 : $coupon_info['amount'];
        $per = round($per, 2, PHP_ROUND_HALF_UP);
        if ($per > $coupon_info['max_discount']) {
            $per = $coupon_info['max_discount'];
        }    // Checking max_discount is not exceeded. If it exceeds, will set max_discount value
        $rem_price = $total_price - $per;
        // Collecting fields to update and then call UpdateOrderInfo
        $fields = [
            'rem_price' => $rem_price,
            'coupon_discount' => $per,
            'coupon_id' => $coupon_info['id'],
        ];
        $updateResults = $this->updateOrderInfo($cart_id, $fields, false);
        if ($updateResults) {
            $order = $this->getOrderByCart($cart_id, true);
            $order->discountonmrp = round($order->total_products_mrp - $order->price_without_delivery, 2, PHP_ROUND_HALF_UP);

            return $order;
        } else {
            return false;
        }
    }

    private function updateOrderInfo(string $cart_id, array $fields, bool $createIfNotExists = false)
    {
        if (! isset($cart_id) || $cart_id === null || empty($fields)) {
            return false;
        }   // There is nothing to be updated. Return false
        if ($createIfNotExists) {
            return Orders::updateOrCreate(['cart_id' => $cart_id], $fields);
        } else {
            return Orders::where('cart_id', $cart_id)->update($fields);
        }
    }

    /**
     * Get the address for this order.
     */
    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id', 'address_id');
    }

    /**
     * Get the user that owns the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the store that owns the order.
     */
    public function store()
    {
        return $this->belongsTo(Stores::class, 'store_id', 'id');
    }

    /**
     * Get the coupon for this order.
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id', 'coupon_id');
    }

    /**
     * Get the cart status for this order.
     */
    public function cartStatus()
    {
        return $this->belongsTo(CartStatus::class, 'cart_id', 'cart_id');
    }

    /**
     * Get the delivery boy for this order.
     */
    public function deliveryBoy()
    {
        return $this->belongsTo(DeliveryBoy::class, 'dboy_id', 'dboy_id');
    }
}
