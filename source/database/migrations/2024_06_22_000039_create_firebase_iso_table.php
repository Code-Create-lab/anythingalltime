<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('firebase_iso')) {
            Schema::create('firebase_iso', function (Blueprint $table) {
                $table->increments('id');
                $table->string('iso_code');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('firebase_iso');
    }
};
