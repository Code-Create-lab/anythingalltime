<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('driver_bank')) {
            Schema::create('driver_bank', function (Blueprint $table) {
                $table->increments('ac_id');
                $table->integer('driver_id');
                $table->string('ac_no');
                $table->string('ifsc');
                $table->string('holder_name');
                $table->string('bank_name');
                $table->string('upi')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_bank');
    }
};
