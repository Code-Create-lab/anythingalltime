<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('membership_bought')) {
            Schema::create('membership_bought', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->integer('mem_id');
                $table->date('mem_start_date');
                $table->date('mem_end_date');
                $table->float('price');
                $table->date('buy_date');
                $table->string('paid_by');
                $table->string('transaction_id')->nullable();
                $table->string('payment_gateway')->nullable();
                $table->string('payment_status')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_bought');
    }
};
