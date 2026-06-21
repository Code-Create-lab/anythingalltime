<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_rating')) {
            Schema::create('product_rating', function (Blueprint $table) {
                $table->increments('rate_id');
                $table->integer('store_id');
                $table->integer('varient_id');
                $table->string('rating');
                $table->longText('description');
                $table->integer('user_id');
                $table->dateTime('created_at');
                $table->dateTime('updated_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_rating');
    }
};
