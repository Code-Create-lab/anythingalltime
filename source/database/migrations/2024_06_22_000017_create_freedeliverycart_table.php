<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('freedeliverycart')) {
            Schema::create('freedeliverycart', function (Blueprint $table) {
                $table->increments('id');
                $table->float('min_cart_value')->default(0);
                $table->float('del_charge')->default(0);
                $table->integer('store_id');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('freedeliverycart');
    }
};
