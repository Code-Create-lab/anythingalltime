<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product')) {
            Schema::create('product', function (Blueprint $table) {
                $table->increments('product_id');
                $table->integer('cat_id');
                $table->string('product_name');
                $table->string('product_image');
                $table->string('type')->default('Regular');
                $table->integer('hide')->default(0);
                $table->integer('added_by')->default(0);
                $table->integer('approved')->default(1);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product');
    }
};
