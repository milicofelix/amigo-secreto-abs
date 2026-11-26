<?php

namespace Tests\Feature;

use App\Models\AmigoSecreto\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AmigoSecretoFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_group_lists_default_to_current_year_and_allow_viewing_past_years(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 26));
        Group::create(['name' => 'Grupo de 2025', 'event_date' => '2025-12-24']);
        Group::create(['name' => 'Grupo de 2026', 'event_date' => '2026-12-24']);
        Group::create(['name' => 'Grupo sem data']);

        $this->withSession(['last_group_dashboard' => 1])->get('/')
            ->assertRedirect(route('dashboard.select', ['year' => 2026]));

        foreach (['/dashboard/select-group', '/groups'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertSee('Grupo de 2026')
                ->assertSee('Grupo sem data')
                ->assertDontSee('Grupo de 2025')
                ->assertDontSee('Sorteio realizado!');

            $this->get($path.'?year=2025')
                ->assertOk()
                ->assertSee('Grupo de 2025')
                ->assertDontSee('Grupo de 2026')
                ->assertDontSee('Grupo sem data');
        }
    }

    public function test_confirmation_appears_once_after_a_successful_draw(): void
    {
        $group = Group::create(['name' => 'Presente', 'event_date' => now()->addDays(2)->toDateString()]);
        $group->participants()->create(['name' => 'Ana']);

        $this->post(route('draw.store', $group))
            ->assertSessionHas('error')
            ->assertSessionMissing('completed_draw_group_id');

        $group->participants()->create(['name' => 'João']);
        $resultUrl = route('draw.show', $group);

        $this->post(route('draw.store', $group))->assertRedirect($resultUrl);
        $this->assertDatabaseCount('assignments', 2);

        $this->get($resultUrl)->assertOk()->assertSee('Sorteio realizado!');
        $this->get($resultUrl)->assertOk()->assertDontSee('Sorteio realizado!');
    }
}
