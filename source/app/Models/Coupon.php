<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $table = 'coupon';

    protected $primaryKey = 'coupon_id';

    public $timestamps = false;

    protected $fillable = [];

    protected $attributes = [];

    protected $guarded = [];

    public function couponIsValid(string $coupon_code, bool $only_exists = false)
    {
        if (! isset($coupon_code) || $coupon_code === null) {
            return false;
        }
        $nowdatime = Carbon::now();

        $coupon = Coupon::where('coupon_code', $coupon_code)
            ->first();

        if ($coupon === null) {
            return false;
        }

        if ($only_exists) {
            return true;
        }

        $start_promotion = new Carbon($coupon->start_date);
        $end_promotion = new Carbon($coupon->end_date);

        return ($nowdatime->greaterThanOrEqualTo($start_promotion) && $nowdatime->lessThanOrEqualTo($end_promotion)) ? $coupon : false;
    }

    public function couponsValidList(string $store_id, bool $ascending = false)
    {
        if (! isset($store_id) || $store_id === null) {
            return false;
        }
        $nowdatime = Carbon::now();

        if ($ascending) {
            $coupons = Coupon::where('store_id', $store_id)
                ->where('start_date', '<=', $nowdatime)
                ->where('end_date', '>=', $nowdatime)
                ->orderBy('coupon_id', 'asc')
                ->get();
        } else {
            $coupons = Coupon::where('store_id', $store_id)
                ->where('start_date', '<=', $nowdatime)
                ->where('end_date', '>=', $nowdatime)
                ->orderBy('coupon_id', 'desc')
                ->get();
        }

        if ($coupons->count() < 1) {
            return false;
        }

        return $coupons;
    }
}
