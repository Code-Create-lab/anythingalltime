<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('store')) {
            Schema::create('store', function (Blueprint $table) {
                $table->id();
                $table->string('store_name');
                $table->string('employee_name');
                $table->string('phone_number');
                $table->string('store_photo')->default('N/A');
                $table->string('city');
                $table->integer('city_id');
                $table->float('admin_share')->default(0);
                $table->string('device_id')->nullable();
                $table->string('email');
                $table->string('password');
                $table->float('del_range');
                $table->string('lat');
                $table->string('lng');
                $table->string('address');
                $table->integer('admin_approval')->default(1);
                $table->integer('orders')->default(1);
                $table->integer('store_status')->default(1);
                $table->string('store_opening_time');
                $table->string('store_closing_time');
                $table->integer('time_interval');
                $table->longText('inactive_reason')->nullable();
                $table->string('id_type')->nullable();
                $table->string('id_number')->nullable();
                $table->string('id_photo')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('store');
    }
}
