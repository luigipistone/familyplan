<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('email_notifications')->default(true)->after('dashboard_widgets');
            $table->boolean('push_notifications')->default(true)->after('email_notifications');
            $table->unsignedSmallInteger('calendar_reminder_minutes')->default(30)->after('push_notifications');
            $table->unsignedTinyInteger('shopping_archive_days')->default(7)->after('calendar_reminder_minutes');
            $table->enum('week_starts_on', ['monday', 'sunday'])->default('monday')->after('shopping_archive_days');
            $table->boolean('compact_dashboard')->default(false)->after('week_starts_on');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'email_notifications',
                'push_notifications',
                'calendar_reminder_minutes',
                'shopping_archive_days',
                'week_starts_on',
                'compact_dashboard',
            ]);
        });
    }
};
