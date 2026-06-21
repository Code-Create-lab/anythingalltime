<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store', function (Blueprint $table) {
            $table->integer('store_id')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('store', function (Blueprint $table) {
            $table->dropColumn('store_id');
        });
    }
};
