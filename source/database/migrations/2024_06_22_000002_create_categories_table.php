<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->increments('cat_id');
                $table->string('title');
                $table->string('slug');
                $table->string('url')->nullable();
                $table->string('image');
                $table->integer('parent')->default(0);
                $table->integer('level');
                $table->string('description')->nullable();
                $table->integer('status')->default(1);
                $table->integer('added_by')->default(0);
                $table->integer('tax_type')->default(0);
                $table->string('tax_name')->nullable();
                $table->float('tax_per')->default(0);
                $table->integer('tx_id')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
