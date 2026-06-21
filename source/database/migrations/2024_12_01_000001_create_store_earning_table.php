<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('store_earning')) {
            Schema::create('store_earning', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedBigInteger('store_id');
                $table->decimal('paid', 10, 2)->default(0);
                $table->decimal('earned', 10, 2)->default(0);
                $table->decimal('remaining', 10, 2)->default(0);
                $table->timestamps();

                $table->foreign('store_id')->references('id')->on('store')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('store_earning');
    }
};
