<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('delivery_boy')) {
            Schema::create('delivery_boy', function (Blueprint $table) {
                $table->increments('dboy_id');
                $table->string('boy_name');
                $table->string('boy_phone');
                $table->string('boy_email')->nullable();
                $table->string('password');
                $table->string('boy_city');
                $table->string('boy_address');
                $table->string('boy_image')->nullable();
                $table->boolean('status')->default(1);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_boy');
    }
};
