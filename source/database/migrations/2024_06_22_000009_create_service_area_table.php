<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('service_area')) {
            Schema::create('service_area', function (Blueprint $table) {
                $table->increments('area_id');
                $table->string('area_name');
                $table->integer('city_id');
                $table->integer('store_id');
                $table->float('delivery_charge')->default(0);
                $table->integer('added_by')->default(0);
                $table->integer('enabled')->default(1);
                $table->string('society_name')->nullable();
                $table->integer('society_id')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_area');
    }
};
