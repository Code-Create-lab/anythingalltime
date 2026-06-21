<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('store_banner', function (Blueprint $table) {
            if (!Schema::hasColumn('store_banner', 'cat_id')) {
                $table->integer('cat_id')->nullable();
            }
            if (!Schema::hasColumn('store_banner', 'image')) {
                $table->string('image')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_banner', function (Blueprint $table) {
            $table->dropColumn(['cat_id', 'image']);
        });
    }
};
