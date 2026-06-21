<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('store_delivery_boy')) {
            Schema::create('store_delivery_boy', function (Blueprint $table) {
                $table->increments('dboy_id');
                $table->string('boy_name');
                $table->string('boy_phone');
                $table->string('boy_city');
                $table->string('password');
                $table->string('device_id')->nullable();
                $table->string('boy_loc');
                $table->string('lat');
                $table->string('lng');
                $table->integer('status')->default(1);
                $table->integer('store_id');
                $table->string('added_by')->default('store');
                $table->integer('ad_dboy_id')->default(0);
                $table->integer('rem_by_admin')->default(0);
                $table->string('id_no')->nullable();
                $table->string('id_photo')->nullable();
                $table->string('id_name')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('store_delivery_boy');
    }
};
