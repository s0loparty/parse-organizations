<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sync_attempts', function (Blueprint $table) {
            $table->unsignedInteger('reviews_processed')->default(0);
            $table->unsignedInteger('reviews_total')->nullable();
            $table->unsignedInteger('next_reviews_page')->nullable()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sync_attempts', function (Blueprint $table) {
            $table->dropColumn([
                'reviews_processed',
                'reviews_total',
                'next_reviews_page',
            ]);
        });
    }
};
