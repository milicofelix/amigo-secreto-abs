<?php

namespace Tests\Feature;

use App\Models\AmigoSecreto\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\HasOrganizerGroups;
use Tests\TestCase;

class AmigoSecretoOrganizerAccessTest extends TestCase
{
    use HasOrganizerGroups, RefreshDatabase;

    public function test_guests_can_enter_the_participant_portal_without_seeing_groups(): void
    {
        $legacy = Group::create(['name' => 'Grupo antigo privado']);
        $participant = $legacy->participants()->create(['name' => 'Participante convidado', 'secret_key' => 'CONVITE-TESTE']);

        $this->get('/')->assertOk()->assertSee('Entrar com meu convite')->assertDontSee($legacy->name);
        $this->get(route('pwa.participant'))->assertOk()->assertDontSee($legacy->name);
        $this->get(route('groups.index'))->assertRedirect(route('login'));
        $this->get(route('groups.edit', $legacy))->assertRedirect(route('login'));
        $this->postJson(route('draw.store', $legacy))->assertUnauthorized();
        $this->get(route('reveal.form', $participant))->assertRedirect(route('pwa.participant'));
        $this->post(route('reveal.verify', $participant), ['password' => 'teste'])->assertForbidden();
        $this->get(route('reveal.by.key', $participant->secret_key))->assertRedirect(route('reveal.form', $participant));
        $this->get(route('reveal.form', $participant))->assertOk();
    }

    public function test_organizers_see_and_manage_only_groups_they_created(): void
    {
        $organizer = $this->signInOrganizer();
        $ownGroup = $this->ownedGroup(['name' => 'Grupo da primeira conta']);
        $otherUser = User::factory()->organizer()->create();
        $otherGroup = new Group(['name' => 'Grupo da segunda conta']);
        $otherGroup->user()->associate($otherUser)->save();
        $legacy = Group::create(['name' => 'Grupo ainda sem criador']);

        foreach (['groups.index', 'dashboard.select'] as $route) {
            $this->get(route($route))->assertOk()->assertHeader('Cache-Control', 'no-store, private')
                ->assertSee($ownGroup->name)->assertDontSee($otherGroup->name)->assertDontSee($legacy->name);
        }

        foreach (['groups.edit', 'amigo.dashboard', 'participants.import', 'draw.show', 'reveal.list', 'dashboard.revealed-friends'] as $route) {
            $this->get(route($route, $otherGroup))->assertForbidden();
        }
        $this->get(route('groups.edit', $legacy))->assertForbidden();
        $this->put(route('groups.update', $otherGroup), ['name' => 'Outro nome'])->assertForbidden();
        $this->delete(route('groups.destroy', $otherGroup))->assertForbidden();
        $this->post(route('participants.store', $otherGroup), ['name' => 'Outra pessoa'])->assertForbidden();
        $this->post(route('draw.store', $otherGroup))->assertForbidden();
        $this->post(route('groups.store'), ['name' => 'Grupo criado pelo login', 'user_id' => $otherUser->id])->assertRedirect();
        $this->assertDatabaseHas('groups', ['name' => 'Grupo criado pelo login', 'user_id' => $organizer->id]);
        $this->assertModelExists($otherGroup);
    }

    public function test_nested_operations_require_the_participant_and_restriction_to_belong_to_the_group(): void
    {
        $this->signInOrganizer();
        $first = $this->ownedGroup(['name' => 'Primeiro']);
        $second = $this->ownedGroup(['name' => 'Segundo']);
        $own = $first->participants()->create(['name' => 'Pessoa A']);
        $other = $second->participants()->create(['name' => 'Pessoa B']);
        $third = $second->participants()->create(['name' => 'Pessoa C']);
        $restriction = $second->restrictions()->create(['participant_id' => $other->id, 'cannot_draw_id' => $third->id]);

        $this->get(route('participants.edit', [$first, $other]))->assertNotFound();
        $this->put(route('participants.update', [$first, $other]), ['name' => 'Alterado'])->assertNotFound();
        $this->delete(route('participants.destroy', [$first, $other]))->assertNotFound();
        $this->get(route('participants.qr', ['group' => $first, 'id' => $other->id]))->assertNotFound();
        $this->delete(route('restrictions.destroy', [$first, $restriction]))->assertNotFound();
        $this->postJson(route('restrictions.store', $first), [
            'participant_id' => $own->id, 'cannot_draw_id' => $other->id,
        ])->assertUnprocessable()->assertJsonValidationErrors('cannot_draw_id');
        $this->assertSame('Pessoa B', $other->fresh()->name);
        $this->assertModelExists($restriction);
    }

    public function test_account_activation_and_legacy_link_require_an_explicit_operator_action(): void
    {
        $account = User::factory()->create();
        $group = Group::create(['name' => 'Grupo sem vínculo']);

        $this->post('/login', ['email' => $account->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->artisan('amigo-secreto:organizador', ['email' => $account->email, '--enable-existing' => true])
            ->assertSuccessful();
        $this->assertTrue($account->fresh()->is_organizer);
        $this->artisan('amigo-secreto:vincular-grupo', ['group' => $group->id, 'email' => $account->email])
            ->assertFailed();
        $this->assertNull($group->fresh()->user_id);

        $arguments = ['group' => $group->id, 'email' => $account->email, '--confirm-creator' => true];
        $this->artisan('amigo-secreto:vincular-grupo', $arguments)->assertSuccessful();
        $this->artisan('amigo-secreto:vincular-grupo', $arguments)->assertFailed();
        $this->assertSame($account->id, $group->fresh()->user_id);
    }

    public function test_operator_can_create_a_new_organizer_with_a_hidden_password_prompt(): void
    {
        $email = 'novo-organizador@example.test';
        $this->artisan('amigo-secreto:organizador', ['email' => $email])
            ->expectsQuestion('Nome', 'Organizador de teste')
            ->expectsQuestion('Senha (mínimo de 12 caracteres)', 'senha-ficticia-123')
            ->expectsQuestion('Repita a senha', 'senha-ficticia-123')
            ->assertSuccessful();

        $this->post('/login', ['email' => $email, 'password' => 'senha-ficticia-123'])->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }
}
