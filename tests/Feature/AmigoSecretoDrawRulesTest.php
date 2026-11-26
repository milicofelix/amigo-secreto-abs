<?php

namespace Tests\Feature;

use App\Jobs\SendDrawEmail;
use App\Models\User;
use App\Services\ParticipantInvitation;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\Concerns\HasOrganizerGroups;
use Tests\TestCase;

class AmigoSecretoDrawRulesTest extends TestCase
{
    use HasOrganizerGroups, RefreshDatabase;

    public function test_impossible_restrictions_leave_no_draw_then_a_feasible_configuration_is_saved(): void
    {
        $this->signInOrganizer();
        Queue::fake();
        $group = $this->ownedGroup(['name' => 'Família']);
        [$ana, $bia, $caio] = collect(['Ana', 'Bia', 'Caio'])
            ->map(fn ($name) => $group->participants()->create(['name' => $name]));
        $group->restrictions()->create(['participant_id' => $ana->id, 'cannot_draw_id' => $bia->id]);
        $restriction = $group->restrictions()->create(['participant_id' => $bia->id, 'cannot_draw_id' => $ana->id]);

        $this->post(route('draw.store', $group))->assertSessionHas('error', fn ($error) => str_contains($error, 'mesmos destinatários'));
        $this->assertDatabaseCount('as_draws', 0);
        $this->assertDatabaseCount('assignments', 0);
        Queue::assertNothingPushed();

        $this->delete(route('restrictions.destroy', [$group, $restriction]))->assertRedirect();
        $this->post(route('draw.store', $group))->assertRedirect(route('draw.show', $group));
        $draw = $group->draws()->active()->sole();
        $this->assertSame([$ana->id => $caio->id, $bia->id => $ana->id, $caio->id => $bia->id],
            $draw->assignments()->orderBy('giver_id')->pluck('receiver_id', 'giver_id')->all());
        $this->assertDatabaseHas('draw_events', ['draw_id' => $draw->id, 'type' => 'confirmed']);
        Queue::assertPushed(SendDrawEmail::class, 1);

        $this->get(route('groups.edit', $group))->assertOk()->assertSee('Histórico do sorteio')
            ->assertDontSee(route('participants.store', $group))->assertDontSee(route('restrictions.store', $group));
        $this->post(route('participants.store', $group), ['name' => 'Outro'])->assertSessionHasErrors('roster');
        $this->put(route('participants.update', [$group, $ana]), ['name' => 'Mudou'])->assertSessionHasErrors('roster');
        $this->delete(route('participants.destroy', [$group, $ana]))->assertSessionHasErrors('roster');
        $this->post(route('restrictions.store', $group), [
            'participant_id' => $ana->id, 'cannot_draw_id' => $caio->id,
        ])->assertSessionHasErrors('roster');
        $this->delete(route('restrictions.destroy', [$group, $group->restrictions()->sole()]))->assertSessionHasErrors('roster');
        $this->delete(route('groups.destroy', $group))->assertSessionHasErrors('group');
    }

    public function test_collective_release_uses_the_scheduled_time_and_ignores_cancelled_pairs(): void
    {
        $owner = $this->signInOrganizer();
        $this->travelTo(now()->startOfMinute());
        $group = $this->ownedGroup(['name' => 'Família', 'event_date' => now()->subDay()->toDateString()]);
        $ana = $group->participants()->create(['name' => 'Ana']);
        $bea = $group->participants()->create(['name' => 'Bea']);
        $old = $group->participants()->create(['name' => 'Antigo participante']);
        $release = now()->addHour()->format('Y-m-d\TH:i');
        $this->patch(route('draw.schedule', $group), ['collective_reveal_at' => $release])->assertSessionHasNoErrors();

        $cancelled = $group->draws()->create(['status' => 'completed', 'drawn_at' => now()->subDay()]);
        $cancelled->assignments()->create(['giver_id' => $ana->id, 'receiver_id' => $old->id]);
        $code = app(ParticipantInvitation::class)->issue($ana);
        $this->post(route('pwa.participant.verify'), ['code' => $code])->assertRedirect();
        $collective = route('dashboard.revealed-friends', $group);
        $this->get($collective)->assertRedirect(route('draw.show', $group));
        $cancel = route('draw.cancel', $group);
        $this->actingAs(User::factory()->organizer()->create())->post($cancel, ['reason' => 'Motivo'])->assertForbidden();
        $this->actingAs($owner)->post($cancel, ['reason' => 'Lista incorreta'])->assertSessionHasNoErrors();
        $this->assertNotNull($cancelled->fresh()->cancelled_at);
        $this->assertDatabaseHas('draw_events', ['draw_id' => $cancelled->id, 'type' => 'cancelled', 'actor_id' => $owner->id]);
        $this->get(route('pwa.participant'))->assertDontSee($old->name);

        $active = $group->draws()->create(['status' => 'completed', 'drawn_at' => now()]);
        $active->assignments()->create(['giver_id' => $ana->id, 'receiver_id' => $bea->id]);
        $this->postJson(route('participant.status.update'), [
            'participant_id' => $ana->id, 'draw_id' => $cancelled->id, 'field' => 'bought_gift', 'value' => 1,
        ])->assertConflict();
        $this->get(route('pwa.participant'))->assertSee($bea->name);
        $this->patch(route('draw.schedule', $group), ['collective_reveal_at' => now()->addHours(2)->format('Y-m-d\TH:i')])
            ->assertSessionHasErrors('collective_reveal_at');
        $this->travel(1)->hour();
        $this->get($collective)->assertOk()->assertSee('Ana')->assertSee('Bea')->assertDontSee($old->name);
        $this->post($cancel, ['reason' => 'Tarde demais'])->assertSessionHasErrors('reason');
    }

    public function test_database_forbids_reusing_a_giver_or_receiver_and_cancelled_draw_jobs_do_not_send(): void
    {
        Mail::fake();
        $this->signInOrganizer();
        $group = $this->ownedGroup(['name' => 'Família']);
        [$a, $b, $c] = collect(['A', 'B', 'C'])->map(fn ($name) =>
            $group->participants()->create(['name' => $name, 'email' => $name.'@example.test'])
        );
        $draw = $group->draws()->create(['status' => 'completed', 'drawn_at' => now(), 'cancelled_at' => now()]);
        $draw->assignments()->create(['giver_id' => $a->id, 'receiver_id' => $b->id]);
        foreach ([[$a->id, $c->id], [$c->id, $b->id]] as [$giver, $receiver]) {
            try {
                DB::transaction(fn () => $draw->assignments()->create(['giver_id' => $giver, 'receiver_id' => $receiver]));
                $this->fail('O banco aceitou um par duplicado neste sorteio.');
            } catch (UniqueConstraintViolationException) {
                $this->assertDatabaseCount('assignments', 1);
            }
        }

        (new SendDrawEmail($draw->id))->handle();
        Mail::assertNothingSent();
    }
}
