<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_notification', function (Blueprint $table) {
            // Add missing columns that the controller and tests expect
            if (!Schema::hasColumn('driver_notification', 'notification_id')) {
                $table->integer('notification_id')->nullable(); // Alternative ID field
            }
            if (!Schema::hasColumn('driver_notification', 'not_id')) {
                $table->integer('not_id')->nullable(); // Another ID field used by controller
            }
            if (!Schema::hasColumn('driver_notification', 'read_by_driver')) {
                $table->integer('read_by_driver')->default(0); // Read status
            }
        });

        // Copy id to notification_id and not_id for compatibility (only if id column exists)
        if (Schema::hasColumn('driver_notification', 'id')) {
            \DB::statement('UPDATE driver_notification SET notification_id = id, not_id = id WHERE notification_id IS NULL OR not_id IS NULL');
        }
    }

    public function down(): void
    {
        Schema::table('driver_notification', function (Blueprint $table) {
            $table->dropColumn(['notification_id', 'not_id', 'read_by_driver']);
        });
    }
};
