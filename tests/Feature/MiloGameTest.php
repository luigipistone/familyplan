<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiloGameTest extends TestCase
{
    use RefreshDatabase;

    public function test_game_and_scene_require_authentication(): void
    {
        $this->get(route('milo'))->assertRedirect(route('login'));
        $this->get(route('milo.scene'))->assertRedirect(route('login'));
    }

    public function test_game_uses_the_ajax_shell_and_an_authenticated_same_origin_scene(): void
    {
        $user = $this->user();
        $this->actingAs($user)->get(route('milo'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertSee('<main class="shell">', false)
            ->assertSee('class="milo-frame"', false)
            ->assertSee(route('milo.scene'))
            ->assertSee('L’isola di Milo');

        $this->get(route('milo.scene'))->assertOk()
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertSee('familyplan.milo.v1.user-'.$user->id, false)
            ->assertSee('/vendor/three/three-0.160.0.min.js', false)
            ->assertSee('/js/milo/chapters.js', false)
            ->assertSee('id="chaptersDialog"', false);
    }

    public function test_scene_progress_is_scoped_to_the_authenticated_account(): void
    {
        $first = $this->user();
        $second = $this->user();
        $this->actingAs($second)->get(route('milo.scene'))->assertOk()
            ->assertSee('familyplan.milo.v1.user-'.$second->id.'"', false)
            ->assertDontSee('familyplan.milo.v1.user-'.$first->id.'"', false);
    }

    public function test_adult_preview_is_explicit_and_propagates_to_the_authenticated_scene(): void
    {
        $this->actingAs($this->user())->get(route('milo', ['prova' => 1]))
            ->assertOk()
            ->assertSee(route('milo.scene', ['prova' => 1]))
            ->assertSee('Prova adulto · nessun salvataggio')
            ->assertSee('Esci dalla prova');
        $this->get(route('milo.scene', ['prova' => 1]))->assertOk()
            ->assertSee('data-test-mode="true"', false);
        $this->get(route('milo.scene'))->assertOk()
            ->assertSee('data-test-mode="false"', false);
    }

    private function user(): User
    {
        return User::create([
            'name' => fake()->unique()->bothify('Milo Test ???? ####'),
            'phone' => fake()->unique()->numerify('3#########'),
            'password' => 'synthetic-test-password',
            'role' => 'familiare',
            'category' => 'altro',
            'is_active' => true,
        ]);
    }
}
