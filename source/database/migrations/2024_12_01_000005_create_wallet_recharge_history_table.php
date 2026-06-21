<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('wallet_recharge_history')) {
            Schema::create('wallet_recharge_history', function (Blueprint $table) {
                $table->increments('wallet_recharge_history_id');
                $table->integer('user_id');
                $table->string('recharge_status');
                $table->float('amount');
                $table->string('payment_gateway');
                $table->string('payment_method')->default('')->nullable();
                $table->date('date_of_recharge');
            });
        } else {
            // Add missing columns to existing table
            Schema::table('wallet_recharge_history', function (Blueprint $table) {
                if (! Schema::hasColumn('wallet_recharge_history', 'date_of_recharge')) {
                    $table->date('date_of_recharge');
                }
                if (! Schema::hasColumn('wallet_recharge_history', 'recharge_status')) {
                    $table->string('recharge_status');
                }
                if (! Schema::hasColumn('wallet_recharge_history', 'payment_gateway')) {
                    $table->string('payment_gateway');
                }
                if (! Schema::hasColumn('wallet_recharge_history', 'payment_method')) {
                    $table->string('payment_method')->default('')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_recharge_history');
    }
};
