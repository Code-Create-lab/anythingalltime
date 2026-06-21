<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('country_code')) {
            Schema::create('country_code', function (Blueprint $table) {
                $table->increments('id');
                $table->string('country_code');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('country_code');
    }
};
