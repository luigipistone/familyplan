<?php

use App\Models\Note;
use App\Models\Reminder;
use App\Models\ShoppingList;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shares', function (Blueprint $table) {
            $table->id();
            $table->string('shareable_type');
            $table->unsignedBigInteger('shareable_id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['shareable_type', 'shareable_id', 'user_id']);
            $table->index(['user_id', 'shareable_type']);
        });

        foreach ([
            'shopping_lists' => ShoppingList::class,
            'reminders' => Reminder::class,
            'notes' => Note::class,
        ] as $table => $model) {
            DB::table($table)->whereNotNull('shared_with')->orderBy('id')->each(function ($row) use ($model) {
                foreach ((array) json_decode($row->shared_with, true) as $userId) {
                    if (DB::table('users')->where('id', (int) $userId)->exists()) {
                        DB::table('shares')->insertOrIgnore([
                            'shareable_type' => $model,
                            'shareable_id' => $row->id,
                            'user_id' => (int) $userId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('shares');
    }
};
