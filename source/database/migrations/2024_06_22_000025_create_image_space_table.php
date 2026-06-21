<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('image_space')) {
            Schema::create('image_space', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('url');
                $table->boolean('aws')->default(0);
                $table->boolean('digital_ocean')->default(0);
                $table->boolean('local')->default(1);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('image_space');
    }
};
