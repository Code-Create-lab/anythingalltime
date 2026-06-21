<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('image_space')) {
            Schema::create('image_space', function (Blueprint $table) {
                $table->increments('space_id');
                $table->integer('digital_ocean')->default(0);
                $table->integer('aws')->default(0);
                $table->integer('same_server')->default(1);
            });

            // Insert default data
            \DB::table('image_space')->insert([
                'digital_ocean' => 0,
                'aws' => 0,
                'same_server' => 1,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('image_space');
    }
};
