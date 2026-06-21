<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('store_banner')) {
            Schema::create('store_banner', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('store_id');
                $table->string('title');
                $table->string('description');
                $table->string('image_url');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_banner');
    }
};
