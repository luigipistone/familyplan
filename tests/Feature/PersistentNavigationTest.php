<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersistentNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_primary_pages_expose_the_replaceable_shell(): void
    {
        $user = User::create([
            'name' => 'Navigazione',
            'phone' => '3999999999',
            'password' => 'password-di-test-123',
            'role' => 'familiare',
            'category' => 'altro',
            'is_active' => true,
        ]);

        foreach (['dashboard', 'calendar', 'shopping', 'family', 'reminders', 'notes', 'contacts', 'profile'] as $route) {
            $this->actingAs($user)
                ->get(route($route))
                ->assertOk()
                ->assertSee('<main class="shell">', false)
                ->assertSee('/js/app.js', false);
        }
    }
}
