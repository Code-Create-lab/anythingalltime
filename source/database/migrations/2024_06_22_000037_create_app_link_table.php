<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('app_link')) {
            Schema::create('app_link', function (Blueprint $table) {
                $table->increments('id');
                $table->string('android_app_link')->nullable();
                $table->string('ios_app_link')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('app_link');
    }
};
