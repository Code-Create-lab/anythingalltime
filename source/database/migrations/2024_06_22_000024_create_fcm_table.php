<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('fcm')) {
            Schema::create('fcm', function (Blueprint $table) {
                $table->increments('id');
                $table->string('server_key')->nullable();
                $table->string('store_server_key')->nullable();
                $table->string('driver_server_key')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fcm');
    }
};
