<?php

use App\Enums\OrganizationSource;
use App\Enums\OrganizationStatus;
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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->enum('source', OrganizationSource::values());
            $table->string('external_id');
            $table->string('name')->nullable();
            $table->decimal('rating', 4, 2)->nullable();
            $table->integer('ratings_count')->nullable();
            $table->integer('reviews_count')->nullable();
            $table->enum('status', OrganizationStatus::values());
            $table->timestamps();

            $table->unique(['source', 'external_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
