<?php

use App\Models\Organization;
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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Organization::class)->constrained();
            $table->string('external_id');
            $table->string('author_name')->nullable();
            $table->text('author_avatar_url')->nullable();
            $table->unsignedTinyInteger('rating');
            $table->text('text')->nullable();
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('dislikes_count')->default(0);
            $table->timestampTz('source_updated_at');
            $table->timestamps();

            $table->unique(['organization_id', 'external_id']);
            $table->index(['organization_id', 'source_updated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
