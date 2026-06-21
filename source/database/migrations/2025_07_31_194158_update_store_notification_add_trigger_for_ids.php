<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update existing records to ensure notification_id and not_id match id (only if columns exist)
        if (Schema::hasColumn('store_notification', 'id') &&
            Schema::hasColumn('store_notification', 'notification_id') &&
            Schema::hasColumn('store_notification', 'not_id')) {
            \DB::statement('UPDATE store_notification SET notification_id = id, not_id = id WHERE notification_id IS NULL OR not_id IS NULL');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
