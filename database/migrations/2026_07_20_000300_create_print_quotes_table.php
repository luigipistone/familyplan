<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('quote_number')->unique();
            $table->string('name');
            $table->string('model_name')->nullable()->index();
            $table->string('customer')->nullable()->index();
            $table->string('status', 20)->default('draft')->index();
            $table->string('currency', 3)->default('EUR');
            $table->string('source_url', 500)->nullable();
            $table->string('external_id')->nullable();
            $table->string('image_path')->nullable();
            $table->string('source_file_path')->nullable();
            $table->json('model_data')->nullable();
            $table->json('calculation_data');
            $table->json('results');
            $table->unsignedInteger('total_cost_cents')->default(0)->index();
            $table->unsignedInteger('final_price_cents')->default(0)->index();
            $table->date('valid_until')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_quotes');
    }
};
