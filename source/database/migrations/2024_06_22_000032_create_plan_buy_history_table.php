<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('plan_buy_history')) {
            Schema::create('plan_buy_history', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->string('type');
                $table->float('amount');
                $table->float('before_recharge');
                $table->float('after_recharge');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_buy_history');
    }
};
