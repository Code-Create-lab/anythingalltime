<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('image_space', function (Blueprint $table) {
            if (!Schema::hasColumn('image_space', 'name')) {
                $table->string('name')->after('space_id');
            }
            if (!Schema::hasColumn('image_space', 'url')) {
                $table->string('url')->after('name');
            }
            if (!Schema::hasColumn('image_space', 'local')) {
                $table->boolean('local')->default(1)->after('same_server');
            }
            if (!Schema::hasColumn('image_space', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        Schema::table('image_space', function (Blueprint $table) {
            $table->dropColumn(['name', 'url', 'local', 'created_at', 'updated_at']);
        });
    }
};