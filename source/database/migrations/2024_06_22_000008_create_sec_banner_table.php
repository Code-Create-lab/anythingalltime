<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSecBannerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('sec_banner')) {
            Schema::create('sec_banner', function (Blueprint $table) {
                $table->id('banner_id');
                $table->string('banner_name');
                $table->string('banner_image')->nullable();
                $table->integer('varient_id');
                $table->string('product_name');
                $table->integer('store_id')->nullable();
                $table->string('image')->nullable();
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
        Schema::dropIfExists('sec_banner');
    }
}
