<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cart_payments')) {
            Schema::create('cart_payments', function (Blueprint $table) {
                $table->increments('id');
                $table->string('cart_id');
                $table->string('payment_method');
                $table->float('amount');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_payments');
    }
};
