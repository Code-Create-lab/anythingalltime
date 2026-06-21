<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('address')) {
            Schema::create('address', function (Blueprint $table) {
                $table->increments('address_id');
                $table->string('type');
                $table->integer('user_id');
                $table->string('receiver_name');
                $table->string('receiver_phone');
                $table->string('city');
                $table->string('society');
                $table->integer('city_id');
                $table->integer('society_id');
                $table->string('house_no');
                $table->string('landmark')->nullable();
                $table->string('state');
                $table->string('pincode');
                $table->string('lat');
                $table->string('lng');
                $table->integer('select_status');
                $table->dateTime('added_at');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('address');
    }
};
