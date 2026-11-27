<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_password_confirmation_is_unavailable_to_signed_in_organizers(): void
    {
        $this->actingAs(User::factory()->organizer()->create());
        $this->get('/confirm-password')->assertNotFound();
        $this->post('/confirm-password', ['password' => 'password'])
            ->assertNotFound()->assertSessionMissing('auth.password_confirmed_at');
    }
}
