<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('wishlist')) {
            Schema::create('wishlist', function (Blueprint $table) {
                $table->increments('wish_id');
                $table->integer('user_id');
                $table->integer('varient_id');
                $table->string('quantity');
                $table->string('unit');
                $table->string('price');
                $table->string('mrp');
                $table->string('product_name');
                $table->longText('description');
                $table->string('varient_image');
                $table->integer('store_id');
                $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlist');
    }
};
