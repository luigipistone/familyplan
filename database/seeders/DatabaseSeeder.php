<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $local = file_exists(base_path('config.local.php')) ? require base_path('config.local.php') : [];
        $admin = $local['admin'] ?? [];

        if (empty($admin['phone']) || empty($admin['password'])) {
            return;
        }

        User::updateOrCreate(
            ['phone' => $admin['phone']],
            [
                'name' => $admin['name'] ?? 'Luigi',
                'email' => $admin['email'] ?? null,
                'password' => Hash::make($admin['password']),
                'role' => 'admin',
                'category' => 'papa',
                'theme' => 'light',
                'dashboard_widgets' => ['calendar', 'shopping', 'family', 'reminders', 'notes'],
                'is_active' => true,
            ]
        );
    }
}
