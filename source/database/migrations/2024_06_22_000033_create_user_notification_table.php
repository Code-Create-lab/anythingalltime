<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_notification')) {
            Schema::create('user_notification', function (Blueprint $table) {
                $table->increments('noti_id');
                $table->integer('user_id');
                $table->string('noti_title', 255);
                $table->string('image', 255)->nullable();
                $table->longText('noti_message');
                $table->integer('read_by_user')->default(0);
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notification');
    }
};
