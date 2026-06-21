<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('store_notification')) {
            Schema::create('store_notification', function (Blueprint $table) {
                $table->id();
                $table->integer('store_id');
                $table->string('notification_title');
                $table->text('notification_msg');
                $table->datetime('notification_date');
                $table->integer('seen')->default(0);
                $table->integer('notification_id')->nullable(); // Compatibility field
                $table->integer('not_id')->nullable(); // Another compatibility field
                $table->integer('read_by_store')->default(0); // Read status
                $table->timestamps();

                // Index for performance
                $table->index(['store_id', 'seen']);
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
        Schema::dropIfExists('store_notification');
    }
};
