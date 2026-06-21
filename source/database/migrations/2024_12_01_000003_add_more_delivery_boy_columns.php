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
            if (!Schema::hasColumn('delivery_boy', 'id_no')) {
                $table->string('id_no')->nullable();
            }
            if (!Schema::hasColumn('delivery_boy', 'id_photo')) {
                $table->string('id_photo')->nullable();
            }
            if (!Schema::hasColumn('delivery_boy', 'id_name')) {
                $table->string('id_name')->nullable();
            }
            if (!Schema::hasColumn('delivery_boy', 'current_lat')) {
                $table->string('current_lat')->nullable();
            }
            if (!Schema::hasColumn('delivery_boy', 'current_lng')) {
                $table->string('current_lng')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('delivery_boy', function (Blueprint $table) {
            $table->dropColumn([
                'id_no',
                'id_photo',
                'id_name',
                'current_lat',
                'current_lng',
            ]);
        });
    }
};
