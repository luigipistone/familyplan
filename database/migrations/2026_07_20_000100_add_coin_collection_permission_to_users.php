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
            $table->boolean('can_manage_coin_collection')->default(false)->after('is_active');
        });

        DB::table('users')->where('role', 'admin')->whereRaw('LOWER(TRIM(name)) = ?', ['luigi'])->update([
            'can_manage_coin_collection' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('can_manage_coin_collection'));
    }
};
