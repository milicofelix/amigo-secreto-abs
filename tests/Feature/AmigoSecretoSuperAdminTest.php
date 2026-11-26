<?php

namespace Tests\Feature;

use App\Models\AmigoSecreto\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\HasOrganizerGroups;
use Tests\TestCase;

class AmigoSecretoSuperAdminTest extends TestCase
{
    use HasOrganizerGroups, RefreshDatabase;

    public function test_global_panel_requires_super_admin_and_displays_only_management_metadata(): void
    {
        $panel = route('super-admin.groups');
        $this->get($panel)->assertRedirect(route('login'));

        $organizer = $this->signInOrganizer();
        $owned = $this->ownedGroup(['name' => 'Grupo do organizador']);
        $owned->participants()->create(['name' => 'Nome reservado', 'email' => 'privado@example.test', 'secret_key' => 'CODIGO-RESERVADO']);
        $unassigned = Group::create(['name' => 'Grupo antigo sem criador', 'event_date' => '2025-12-15']);
        $this->get($panel)->assertForbidden();

        $admin = User::factory()->organizer()->create(['is_super_admin' => true]);
        $this->actingAs($admin);
        $mine = $this->ownedGroup(['name' => 'Grupo criado pelo administrador']);

        $this->get($panel)->assertOk()->assertHeader('Cache-Control', 'no-store, private')
            ->assertSee($owned->name)->assertSee($unassigned->name)->assertSee($mine->name)
            ->assertSee('Sem vínculo')->assertDontSee('Nome reservado')
            ->assertDontSee('privado@example.test')->assertDontSee('CODIGO-RESERVADO');

        $this->get(route('groups.index'))->assertOk()->assertSee($mine->name)->assertDontSee($owned->name);
        $this->get(route('groups.edit', $mine))->assertOk();
        $this->get(route('groups.edit', $owned))->assertForbidden();
        $this->get(route('dashboard.revealed-friends', $owned))->assertForbidden();
        $this->post(route('draw.store', $owned))->assertForbidden();
        $this->put(route('groups.update', $owned), ['name' => 'Alterado'])->assertForbidden();
        $this->delete(route('groups.destroy', $owned))->assertForbidden();
        $this->assertEquals($organizer->id, $owned->fresh()->user_id);
    }

    public function test_filters_can_be_combined_and_use_creation_year_for_groups_without_event_date(): void
    {
        $organizer = $this->signInOrganizer();
        $completed = $this->ownedGroup(['name' => 'Evento concluído', 'event_date' => '2025-12-24']);
        $completed->draws()->create(['drawn_at' => now(), 'status' => 'completed']);
        $pending = $this->ownedGroup(['name' => 'Evento anterior pendente', 'event_date' => '2025-12-24']);
        $undated = $this->ownedGroup(['name' => 'Evento atual sem data']);
        $unassigned = Group::create(['name' => 'Evento antigo sem vínculo', 'event_date' => '2025-11-25']);
        $this->actingAs(User::factory()->organizer()->create(['is_super_admin' => true]));

        $this->get(route('super-admin.groups', ['year' => 2025, 'creator' => $organizer->id, 'draw' => 'completed']))
            ->assertOk()->assertSee($completed->name)->assertDontSee($pending->name)
            ->assertDontSee($undated->name)->assertDontSee($unassigned->name);
        $this->get(route('super-admin.groups', ['year' => $undated->eventYear(), 'draw' => 'pending']))
            ->assertOk()->assertSee($undated->name);
        $this->get(route('super-admin.groups', ['creator' => 'unassigned', 'draw' => 'pending']))
            ->assertOk()->assertSee($unassigned->name)->assertDontSee($pending->name);
    }

    public function test_super_admin_role_is_managed_through_the_terminal_and_changes_landing(): void
    {
        $account = User::factory()->organizer()->create();
        $disabled = User::factory()->create();
        $this->artisan('amigo-secreto:super-admin', ['email' => $disabled->email])->assertFailed();
        $this->artisan('amigo-secreto:super-admin', ['email' => $account->email])->assertSuccessful();

        $this->post('/login', ['email' => $account->email, 'password' => 'password'])
            ->assertRedirect(route('super-admin.groups', absolute: false));
        $this->get('/')->assertRedirect(route('super-admin.groups'));
        $this->artisan('amigo-secreto:super-admin', ['email' => $account->email, '--revoke' => true])->assertSuccessful();
        $this->actingAs($account->fresh())->get(route('super-admin.groups'))->assertForbidden();
        $this->get(route('groups.index'))->assertOk();
    }

    public function test_creator_confirmation_history_is_recorded_once_and_is_visible_to_super_admin(): void
    {
        $organizer = User::factory()->organizer()->create(['name' => 'Criador confirmado']);
        $group = Group::create(['name' => 'Grupo do histórico']);
        $arguments = ['group' => $group->id, 'email' => $organizer->email, '--confirm-creator' => true];
        $this->artisan('amigo-secreto:vincular-grupo', $arguments)->assertSuccessful();
        $this->artisan('amigo-secreto:vincular-grupo', $arguments)->assertFailed();
        $this->assertDatabaseHas('group_creator_confirmations', ['group_id' => $group->id, 'creator_id' => $organizer->id]);
        $this->assertDatabaseCount('group_creator_confirmations', 1);

        $admin = User::factory()->organizer()->create(['is_super_admin' => true]);
        $this->actingAs($admin)->get(route('super-admin.groups'))
            ->assertOk()->assertSeeInOrder(['Histórico de confirmação de criadores', $group->name, $organizer->name]);
    }
}
