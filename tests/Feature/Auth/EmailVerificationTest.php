<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_organizer_activation_is_an_operator_action_and_does_not_depend_on_email_verification(): void
    {
        $account = User::factory()->unverified()->create();

        $this->post('/login', ['email' => $account->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->artisan('amigo-secreto:organizador', ['email' => $account->email, '--enable-existing' => true])
            ->assertSuccessful();
        $this->post('/login', ['email' => $account->email, 'password' => 'password'])->assertRedirect('/dashboard');
        $this->get('/verify-email')->assertNotFound();
        $this->post('/email/verification-notification')->assertNotFound();
        $this->assertAuthenticatedAs($account);
        $this->assertNull($account->fresh()->email_verified_at);
    }
}
