<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('wallet_recharge_history')) {
            Schema::create('wallet_recharge_history', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->float('amount');
                $table->string('payment_method')->nullable();
                $table->string('transaction_id')->nullable();
                $table->string('status')->default('pending');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_recharge_history');
    }
};
