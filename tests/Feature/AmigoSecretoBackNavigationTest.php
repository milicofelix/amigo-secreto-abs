<?php

namespace Tests\Feature;

use App\Models\Rifas\Rifa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\HasOrganizerGroups;
use Tests\TestCase;

class AmigoSecretoBackNavigationTest extends TestCase
{
    use HasOrganizerGroups, RefreshDatabase;

    public function test_back_links_always_point_to_their_parent_page(): void
    {
        $this->signInOrganizer();
        $group = $this->ownedGroup(['name' => 'Grupo atual', 'event_date' => now()->addWeek()->toDateString()]);
        $olderGroup = $this->ownedGroup(['name' => 'Grupo anterior', 'event_date' => now()->subYear()->toDateString()]);
        $participant = $group->participants()->create(['name' => 'Participante', 'secret_key' => 'CODIGOTESTE']);

        $dashboard = route('amigo.dashboard', $group, absolute: false);
        $groups = route('groups.index', ['year' => $group->eventYear()], absolute: false);
        $draw = route('draw.show', $group, absolute: false);
        $script = url('/service-worker.js');

        $pages = [
            [route('reveal.list', $group), $dashboard],
            [route('participants.qr', ['group' => $group, 'participant' => $participant->id]), route('groups.edit', $group, absolute: false)],
            [route('dashboard.start-draw', $group), $dashboard],
            [$draw, $groups],
            [route('participants.edit', [$group, $participant]), route('groups.edit', $group, absolute: false)],
            [route('participants.create', $group), route('groups.edit', $group, absolute: false)],
            [route('participants.import', $group), route('groups.edit', $group, absolute: false)],
            [route('dashboard.revealed-friends', $olderGroup), route('amigo.dashboard', $olderGroup, absolute: false)],
        ];

        foreach ($pages as [$page, $destination]) {
            $this->withHeader('Referer', $script)->get($page)
                ->assertOk()
                ->assertSee('href="'.$destination.'"', false)
                ->assertDontSee('href="'.$script.'"', false);
            $this->get($destination)->assertOk();
        }
    }

    public function test_pix_back_link_returns_to_its_raffle_when_the_legacy_module_is_enabled(): void
    {
        config(['features.raffles' => true]);
        $rifa = Rifa::create(['titulo' => 'Rifa de teste', 'valor' => 10]);
        $nome = $rifa->nomes()->create(['nome' => 'Número de teste']);
        $destination = route('rifas.show', $rifa, absolute: false);
        $script = url('/service-worker.js');

        $this->withHeader('Referer', $script)->get(route('rifas.pix', $nome))->assertOk()
            ->assertSee('href="'.$destination.'"', false)
            ->assertDontSee('href="'.$script.'"', false);
        $this->get($destination)->assertOk();
    }
}
