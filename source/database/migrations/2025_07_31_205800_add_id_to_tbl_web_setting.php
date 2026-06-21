<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_web_setting', function (Blueprint $table) {
            $table->integer('id')->nullable()->after('set_id');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_web_setting', function (Blueprint $table) {
            $table->dropColumn('id');
        });
    }
};
