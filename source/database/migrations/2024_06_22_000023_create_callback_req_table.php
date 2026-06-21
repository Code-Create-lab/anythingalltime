<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('callback_req')) {
            Schema::create('callback_req', function (Blueprint $table) {
                $table->increments('callback_req_id');
                $table->string('user_name');
                $table->string('user_phone');
                $table->integer('user_id');
                $table->integer('store_id');
                $table->integer('processed')->default(0);
                $table->date('date');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('callback_req');
    }
};
