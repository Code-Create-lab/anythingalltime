<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('membership_plan')) {
            Schema::create('membership_plan', function (Blueprint $table) {
                $table->increments('plan_id');
                $table->string('plan_name');
                $table->float('price');
                $table->integer('days');
                $table->boolean('free_delivery')->default(0);
                $table->boolean('instant_delivery')->default(0);
                $table->integer('reward')->default(0);
                $table->string('plan_description')->nullable();
                $table->boolean('hide')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_plan');
    }
};
