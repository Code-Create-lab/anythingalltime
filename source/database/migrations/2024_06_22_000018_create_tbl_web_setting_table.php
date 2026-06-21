<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tbl_web_setting')) {
            Schema::create('tbl_web_setting', function (Blueprint $table) {
                $table->increments('set_id');
                $table->string('icon');
                $table->string('name');
                $table->string('favicon');
                $table->integer('number_limit');
                $table->integer('last_loc')->default(0);
                $table->longText('footer_text')->nullable();
                $table->integer('live_chat')->default(1);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_web_setting');
    }
};
