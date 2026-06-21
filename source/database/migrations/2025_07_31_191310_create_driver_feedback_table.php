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
        if (! Schema::hasTable('driver_feedback')) {
            Schema::create('driver_feedback', function (Blueprint $table) {
                $table->id();
                $table->integer('dboy_id');
                $table->text('message');
                $table->string('subject');
                $table->string('priority')->default('medium');
                $table->string('status')->default('open');
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
        Schema::dropIfExists('driver_feedback');
    }
};
