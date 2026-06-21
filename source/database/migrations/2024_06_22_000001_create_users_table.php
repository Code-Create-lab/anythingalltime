<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('email')->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password')->nullable();
                $table->string('remember_token', 100)->nullable();
                $table->string('user_phone')->nullable();
                $table->string('device_id')->nullable();
                $table->string('user_image')->default('N/A');
                $table->integer('user_city')->nullable();
                $table->integer('user_area')->nullable();
                $table->string('otp_value')->nullable();
                $table->integer('status')->default(1);
                $table->float('wallet')->default(0);
                $table->integer('rewards')->default(0);
                $table->integer('is_verified')->default(0);
                $table->integer('block')->default(2);
                $table->date('reg_date');
                $table->integer('app_update')->default(1);
                $table->string('facebook_id')->nullable();
                $table->string('referral_code')->nullable();
                $table->integer('membership')->default(0);
                $table->date('mem_plan_start')->nullable();
                $table->date('mem_plan_expiry')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
