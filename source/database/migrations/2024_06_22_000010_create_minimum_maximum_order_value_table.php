<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('minimum_maximum_order_value')) {
            Schema::create('minimum_maximum_order_value', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('store_id');
                $table->integer('min_value');
                $table->integer('max_value');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('minimum_maximum_order_value');
    }
};
