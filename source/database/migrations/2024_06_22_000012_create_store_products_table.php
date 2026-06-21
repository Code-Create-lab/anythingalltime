<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('store_products')) {
            Schema::create('store_products', function (Blueprint $table) {
                $table->increments('p_id');
                $table->integer('varient_id');
                $table->integer('stock');
                $table->integer('store_id');
                $table->float('mrp');
                $table->float('price');
                $table->integer('min_ord_qty')->default(1);
                $table->integer('max_ord_qty')->default(100);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('store_products');
    }
};
