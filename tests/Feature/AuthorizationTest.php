<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_change_another_users_contact(): void
    {
        $owner = $this->user('Proprietario');
        $other = $this->user('Familiare');
        $contact = Contact::create(['owner_id' => $owner->id, 'name' => 'Mario', 'surname' => 'Rossi']);

        $this->actingAs($other)->patch(route('contacts.update', $contact), [
            'name' => 'Modificato',
            'surname' => 'Rossi',
        ])->assertForbidden();

        $this->assertDatabaseHas('contacts', ['id' => $contact->id, 'name' => 'Mario']);
    }

    public function test_admin_can_change_a_family_contact(): void
    {
        $owner = $this->user('Proprietario');
        $admin = $this->user('Admin', ['role' => 'admin']);
        $contact = Contact::create(['owner_id' => $owner->id, 'name' => 'Mario', 'surname' => 'Rossi']);

        $this->actingAs($admin)->patch(route('contacts.update', $contact), [
            'name' => 'Marco',
            'surname' => 'Rossi',
        ])->assertRedirect();

        $this->assertDatabaseHas('contacts', ['id' => $contact->id, 'name' => 'Marco']);
    }

    public function test_coin_collection_requires_the_dedicated_permission(): void
    {
        $admin = $this->user('Admin', ['role' => 'admin']);
        $collector = $this->user('Collezionista', ['can_manage_coin_collection' => true]);

        $this->actingAs($admin)->get(route('coins'))->assertForbidden();
        $this->actingAs($collector)->get(route('coins'))->assertOk();
    }

    private function user(string $name, array $attributes = []): User
    {
        return User::create(array_merge([
            'name' => $name,
            'phone' => fake()->unique()->numerify('3#########'),
            'password' => 'password-di-test-123',
            'role' => 'familiare',
            'category' => 'altro',
            'is_active' => true,
        ], $attributes));
    }
}
