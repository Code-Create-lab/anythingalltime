<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('currency')) {
            Schema::create('currency', function (Blueprint $table) {
                $table->increments('id');
                $table->string('currency_sign');
                $table->string('currency_name');
                $table->string('currency_symbol')->nullable();
                $table->string('currency_code')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('currency');
    }
};
