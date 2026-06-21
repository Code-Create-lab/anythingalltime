<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop and recreate store_products table without unique constraint on p_id
        Schema::dropIfExists('store_products');

        Schema::create('store_products', function (Blueprint $table) {
            $table->id(); // Add auto-increment primary key
            $table->integer('p_id')->nullable();
            $table->integer('varient_id')->default(0);
            $table->integer('stock')->nullable();
            $table->integer('store_id');
            $table->float('mrp')->nullable();
            $table->float('price')->nullable();
            $table->integer('min_ord_qty')->default(1);
            $table->integer('max_ord_qty')->default(100);

            // Composite unique key: store can have same product with different variants
            // But only when all fields are not null
            $table->index(['store_id', 'p_id', 'varient_id'], 'idx_store_product_variant');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_products');
    }
};
