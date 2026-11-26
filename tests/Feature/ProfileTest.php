<?php

namespace Tests\Feature;

use App\Models\AmigoSecreto\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_profile_routes_cannot_modify_or_remove_an_organizer_and_their_groups(): void
    {
        $organizer = User::factory()->organizer()->create();
        $group = new Group(['name' => 'Grupo existente']);
        $group->user()->associate($organizer)->save();
        $this->actingAs($organizer);

        $this->get('/profile')->assertNotFound();
        $this->patch('/profile', ['name' => 'Outro', 'email' => 'outro@example.test'])->assertNotFound();
        $this->delete('/profile', ['password' => 'password'])->assertNotFound();

        $this->assertSame($organizer->email, $organizer->fresh()->email);
        $this->assertSame($organizer->name, $organizer->fresh()->name);
        $this->assertSame($organizer->id, $group->fresh()->user_id);
    }
}
