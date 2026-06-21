<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('coupon')) {
            $this->createCouponNewTable();
        } else {
            Schema::rename('coupon', 'coupon_backup');
            $this->createCouponNewTable();
        }
    }

    /**
     * Reverse the migrations. If user didn't erased backup it will try to revert migration to original point
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupon');
        if (Schema::hasTable('coupon_backup')) {
            Schema::rename('coupon_backup', 'coupon');
        }
    }

    private function createCouponNewTable()
    {
        Schema::create('coupon', function (Blueprint $table) {
            $table->increments('coupon_id');
            $table->string('coupon_name');
            $table->string('coupon_image');
            $table->string('coupon_code');
            $table->string('coupon_description');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->integer('cart_value');
            $table->integer('amount');
            $table->string('type');
            $table->integer('uses_restriction')->default(1);
            $table->integer('store_id');
        });
    }
}
