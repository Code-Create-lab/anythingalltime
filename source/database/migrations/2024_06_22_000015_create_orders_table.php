<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->increments('order_id');
                $table->integer('user_id');
                $table->integer('store_id');
                $table->integer('address_id');
                $table->string('cart_id');
                $table->float('total_price');
                $table->float('price_without_delivery');
                $table->float('total_products_mrp');
                $table->string('payment_method')->nullable();
                $table->float('paid_by_wallet')->default(0);
                $table->float('rem_price')->default(0);
                $table->float('avg_tax_per')->nullable();
                $table->float('total_tax_price')->nullable();
                $table->date('order_date');
                $table->date('delivery_date');
                $table->float('delivery_charge')->default(0);
                $table->string('time_slot');
                $table->integer('dboy_id')->default(0);
                $table->string('order_status')->default('Pending');
                $table->string('user_signature')->nullable();
                $table->string('cancelling_reason')->nullable();
                $table->integer('coupon_id')->default(0);
                $table->float('coupon_discount')->default(0);
                $table->string('payment_status')->nullable();
                $table->integer('cancel_by_store')->default(0);
                $table->integer('dboy_incentive')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
