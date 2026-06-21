<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_varient')) {
            Schema::create('product_varient', function (Blueprint $table) {
                $table->increments('varient_id');
                $table->integer('product_id');
                $table->integer('quantity');
                $table->string('unit');
                $table->float('base_mrp')->nullable();
                $table->float('base_price');
                $table->longText('description');
                $table->string('varient_image');
                $table->string('ean')->nullable();
                $table->integer('approved')->default(1);
                $table->integer('added_by')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_varient');
    }
};
