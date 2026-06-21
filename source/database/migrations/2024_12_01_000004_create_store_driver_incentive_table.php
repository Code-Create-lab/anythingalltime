<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('store_driver_incentive')) {
            Schema::create('store_driver_incentive', function (Blueprint $table) {
                $table->increments('id');
                $table->string('incentive');
                $table->integer('store_id');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('store_driver_incentive');
    }
};
