<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('theme', 'system')->update(['theme' => 'light']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY theme ENUM('light', 'dark') NOT NULL DEFAULT 'light'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY theme ENUM('system', 'light', 'dark') NOT NULL DEFAULT 'system'");
        }
    }
};
