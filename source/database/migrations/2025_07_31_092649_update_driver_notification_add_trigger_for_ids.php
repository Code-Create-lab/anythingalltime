<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // For SQLite, we'll use a simple SQL update approach
        // Update existing records to ensure notification_id and not_id match id (only if id column exists)
        if (Schema::hasColumn('driver_notification', 'id')) {
            \DB::statement('UPDATE driver_notification SET notification_id = id, not_id = id WHERE notification_id IS NULL OR not_id IS NULL');
        }

        // For future records, we can use a default value approach in the model
        // But for now, let's modify the existing records
    }

    public function down(): void
    {
        // Nothing to rollback
    }
};
