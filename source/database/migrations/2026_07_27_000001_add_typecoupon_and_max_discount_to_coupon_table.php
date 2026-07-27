<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('coupon')) {
            return;
        }

        Schema::table('coupon', function (Blueprint $table) {
            if (! Schema::hasColumn('coupon', 'typecoupon')) {
                // 'default' = regular coupon, 'forder' = first-order-only coupon.
                $table->string('typecoupon', 20)->default('default')->after('type');
            }

            if (! Schema::hasColumn('coupon', 'max_discount')) {
                $table->integer('max_discount')->default(0)->after('amount');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('coupon')) {
            return;
        }

        Schema::table('coupon', function (Blueprint $table) {
            if (Schema::hasColumn('coupon', 'typecoupon')) {
                $table->dropColumn('typecoupon');
            }

            if (Schema::hasColumn('coupon', 'max_discount')) {
                $table->dropColumn('max_discount');
            }
        });
    }
};
