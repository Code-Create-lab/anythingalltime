<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('deal_product')) {
            Schema::create('deal_product', function (Blueprint $table) {
                $table->increments('deal_id');
                $table->integer('varient_id');
                $table->float('deal_price');
                $table->dateTime('valid_from');
                $table->dateTime('valid_to');
                $table->integer('status');
                $table->integer('store_id');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_product');
    }
};
