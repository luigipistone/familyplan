<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('country', 100);
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('title');
            $table->decimal('denomination_eur', 8, 2)->nullable();
            $table->string('type', 80)->nullable();
            $table->string('condition', 80)->nullable();
            $table->string('rarity', 80)->nullable();
            $table->decimal('estimated_min_eur', 10, 2)->nullable();
            $table->decimal('estimated_max_eur', 10, 2)->nullable();
            $table->decimal('face_value_eur', 8, 2)->nullable();
            $table->boolean('probable_duplicate')->default(false);
            $table->decimal('identification_confidence', 4, 3)->nullable();
            $table->boolean('manual_verification')->default(false);
            $table->string('image_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['owner_id', 'code']);
            $table->index(['owner_id', 'country', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coins');
    }
};
