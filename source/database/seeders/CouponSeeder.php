<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\QueryException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(ConnectionInterface $connection)
    {
        if (Schema::hasTable('coupon_backup')) {
            $connection->table('coupon_backup')
                ->orderBy('coupon_id')
                ->chunk(200, function ($coupons) {
                    foreach ($coupons as $coupon) {
                        $coupon = (array) $coupon;
                        $coupon_new = new Coupon;
                        $coupon_new->coupon_name = $coupon['coupon_name'];
                        $coupon_new->coupon_image = $coupon['coupon_image'];
                        $coupon_new->coupon_code = $coupon['coupon_code'];
                        $coupon_new->coupon_description = $coupon['coupon_description'];
                        $coupon_new->start_date = $coupon['start_date'];
                        $coupon_new->end_date = $coupon['end_date'];
                        $coupon_new->cart_value = $coupon['cart_value'];
                        $coupon_new->amount = $coupon['amount'];
                        $coupon_new->type = ($coupon['type'] == 'percentage') ? 'percent' : $coupon['type'];
                        if (isset($coupon['uses_restriction'])) {
                            $coupon_new->uses_restriction = $coupon['uses_restriction'];
                        }
                        $coupon_new->store_id = $coupon['store_id'];

                        try {
                            $coupon_new->save();
                        } catch (QueryException $query_error) {
                            echo 'There was an exception while copying old records to coupon table';
                        }
                    }
                });

            Schema::dropIfExists('coupon_backup');
        }
    }
}
