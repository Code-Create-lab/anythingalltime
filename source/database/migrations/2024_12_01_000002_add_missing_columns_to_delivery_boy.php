<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_boy', function (Blueprint $table) {
            if (!Schema::hasColumn('delivery_boy', 'device_id')) {
                $table->string('device_id')->nullable();
            }
            if (!Schema::hasColumn('delivery_boy', 'boy_loc')) {
                $table->string('boy_loc')->nullable();
            }
            if (!Schema::hasColumn('delivery_boy', 'lat')) {
                $table->string('lat')->nullable();
            }
            if (!Schema::hasColumn('delivery_boy', 'lng')) {
                $table->string('lng')->nullable();
            }
            if (!Schema::hasColumn('delivery_boy', 'store_id')) {
                $table->unsignedBigInteger('store_id')->nullable();
            }
            if (!Schema::hasColumn('delivery_boy', 'store_dboy_id')) {
                $table->integer('store_dboy_id')->default(0);
            }
            if (!Schema::hasColumn('delivery_boy', 'added_by')) {
                $table->string('added_by')->default('admin');
            }
            if (!Schema::hasColumn('delivery_boy', 'image')) {
                $table->string('image')->default('default.jpg');
            }
        });
    }

    public function down(): void
    {
        Schema::table('delivery_boy', function (Blueprint $table) {
            $table->dropColumn([
                'device_id',
                'boy_loc',
                'lat',
                'lng',
                'store_id',
                'store_dboy_id',
                'added_by',
                'image',
            ]);
        });
    }
};
