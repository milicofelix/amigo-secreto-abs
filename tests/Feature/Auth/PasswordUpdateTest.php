<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_password_update_route_cannot_change_an_organizers_password(): void
    {
        $account = User::factory()->organizer()->create();
        $this->actingAs($account)->put('/password', [
            'current_password' => 'password',
            'password' => 'nova-senha-ficticia',
            'password_confirmation' => 'nova-senha-ficticia',
        ])->assertNotFound();

        $this->assertSame($account->password, $account->fresh()->password);
    }
}
