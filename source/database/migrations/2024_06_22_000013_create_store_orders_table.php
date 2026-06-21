<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('store_orders')) {
            Schema::create('store_orders', function (Blueprint $table) {
                $table->increments('store_order_id');
                $table->string('product_name');
                $table->string('varient_image');
                $table->float('quantity');
                $table->string('unit');
                $table->integer('varient_id');
                $table->integer('qty');
                $table->float('price');
                $table->float('total_mrp');
                $table->string('order_cart_id');
                $table->dateTime('order_date');
                $table->integer('store_approval')->default(1);
                $table->integer('store_id');
                $table->longText('description');
                $table->integer('tx_per')->nullable();
                $table->float('price_without_tax')->nullable();
                $table->float('tx_price')->nullable();
                $table->string('tx_name')->nullable();
                $table->string('type')->default('Regular');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('store_orders');
    }
};
