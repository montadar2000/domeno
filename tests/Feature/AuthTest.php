<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_person_can_register_with_a_username_and_password_and_enter_immediately(): void
    {
        $this->post(route('register.store'), [
            'username' => 'علي',
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['username' => 'علي']);
        $this->assertNull(User::query()->where('username', 'علي')->first()->email_verified_at);
    }

    public function test_a_person_can_log_in_with_the_same_username_and_password(): void
    {
        User::factory()->create([
            'username' => 'hassan',
            'password' => 'secret',
        ]);

        $this->post(route('login.store'), [
            'username' => 'hassan',
            'password' => 'secret',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticated();
    }

    public function test_a_wrong_password_is_rejected(): void
    {
        User::factory()->create([
            'username' => 'hassan',
            'password' => 'secret',
        ]);

        $this->post(route('login.store'), [
            'username' => 'hassan',
            'password' => 'nope',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
    }
}
