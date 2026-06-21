<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('smsby', function (Blueprint $table) {
            if (!Schema::hasColumn('smsby', 'twilio')) {
                $table->boolean('twilio')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('smsby', function (Blueprint $table) {
            $table->dropColumn('twilio');
        });
    }
};
