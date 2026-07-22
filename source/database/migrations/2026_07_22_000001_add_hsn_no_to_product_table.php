<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product') && ! Schema::hasColumn('product', 'hsn_no')) {
            Schema::table('product', function (Blueprint $table) {
                $table->string('hsn_no', 20)->nullable()->after('type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('product') && Schema::hasColumn('product', 'hsn_no')) {
            Schema::table('product', function (Blueprint $table) {
                $table->dropColumn('hsn_no');
            });
        }
    }
};
