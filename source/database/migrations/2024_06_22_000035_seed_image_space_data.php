<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Insert default image space configuration if table is empty
        if (DB::table('image_space')->count() == 0) {
            DB::table('image_space')->insert([
                'name' => 'Local Storage',
                'url' => '/storage',
                'aws' => 0,
                'digital_ocean' => 0,
                'local' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('image_space')->truncate();
    }
};
