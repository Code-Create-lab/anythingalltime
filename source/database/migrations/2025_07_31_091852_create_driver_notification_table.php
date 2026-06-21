<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('driver_notification')) {
            Schema::create('driver_notification', function (Blueprint $table) {
                $table->id();
                $table->integer('dboy_id');
                $table->string('notification_title');
                $table->text('notification_msg');
                $table->datetime('notification_date');
                $table->integer('seen')->default(0);
                $table->string('type')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_notification');
    }
};
