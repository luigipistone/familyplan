<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('movie_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('tmdb_id');
            $table->string('media_type', 10);
            $table->string('status', 20)->default('watchlist');
            $table->boolean('is_favorite')->default(false);
            $table->boolean('is_shared')->default(false);
            $table->unsignedTinyInteger('rating')->nullable();
            $table->json('metadata');
            $table->timestamps();
            $table->unique(['user_id', 'tmdb_id', 'media_type']);
            $table->index(['is_shared', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movie_entries');
    }
};
