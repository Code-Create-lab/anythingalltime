<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('store_products')) {
            Schema::create('store_products', function (Blueprint $table) {
                $table->integer('p_id');
                $table->integer('varient_id')->default(0);
                $table->integer('stock');
                $table->integer('store_id');
                $table->float('mrp');
                $table->float('price');
                $table->integer('min_ord_qty')->default(1);
                $table->integer('max_ord_qty')->default(100);
            });
        } else {
            // Add missing columns to existing table
            Schema::table('store_products', function (Blueprint $table) {
                if (! Schema::hasColumn('store_products', 'varient_id')) {
                    $table->integer('varient_id')->default(0);
                }
                if (! Schema::hasColumn('store_products', 'mrp')) {
                    $table->float('mrp')->default(0);
                }
                if (! Schema::hasColumn('store_products', 'price')) {
                    $table->float('price')->default(0);
                }
                if (! Schema::hasColumn('store_products', 'min_ord_qty')) {
                    $table->integer('min_ord_qty')->default(1);
                }
                if (! Schema::hasColumn('store_products', 'max_ord_qty')) {
                    $table->integer('max_ord_qty')->default(100);
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('store_products');
    }
};
