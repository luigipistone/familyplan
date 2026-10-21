<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'mother_id')) {
                $table->foreignId('mother_id')->nullable()->after('parent_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('users', 'father_id')) {
                $table->foreignId('father_id')->nullable()->after('mother_id')->constrained('users')->nullOnDelete();
            }
        });

        DB::table('users')
            ->where('category', 'figlio')
            ->whereNotNull('parent_id')
            ->whereNull('mother_id')
            ->whereNull('father_id')
            ->orderBy('id')
            ->each(function ($child): void {
                $parent = DB::table('users')->where('id', $child->parent_id)->first();
                if (! $parent) {
                    return;
                }

                $update = match ($parent->category) {
                    'mamma' => ['mother_id' => $parent->id],
                    'papa' => ['father_id' => $parent->id],
                    default => [],
                };

                if ($update !== []) {
                    DB::table('users')->where('id', $child->id)->update($update);
                }
            });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE users ADD UNIQUE INDEX users_name_unique (name)');
        } else {
            Schema::table('users', fn (Blueprint $table) => $table->unique('name'));
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE users DROP INDEX users_name_unique');
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'father_id')) {
                $table->dropConstrainedForeignId('father_id');
            }
            if (Schema::hasColumn('users', 'mother_id')) {
                $table->dropConstrainedForeignId('mother_id');
            }
        });
    }
};
