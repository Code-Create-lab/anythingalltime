<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('driver_incentive')) {
            Schema::create('driver_incentive', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('dboy_id');
                $table->string('earned_till_now');
                $table->string('paid_till_now');
                $table->string('remaining');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_incentive');
    }
};
