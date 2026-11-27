<?php

namespace Tests\Feature;

use App\Mail\ParticipantAccessRecovery;
use App\Services\ParticipantInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\HasOrganizerGroups;
use Tests\TestCase;

class AmigoSecretoParticipantNavigationTest extends TestCase
{
    use HasOrganizerGroups, RefreshDatabase;

    public function test_participant_can_read_their_group_but_not_manage_it_or_see_other_groups(): void
    {
        $this->signInOrganizer();
        $group = $this->ownedGroup(['name' => 'Meu grupo', 'collective_reveal_at' => now()->addHour()]);
        $otherGroup = $this->ownedGroup(['name' => 'Outro grupo']);
        $member = $group->participants()->create(['name' => 'Convidada']);
        $receiver = $group->participants()->create([
            'name' => 'Outra pessoa', 'email' => 'reservado@example.test', 'wishlist' => 'Preferência privada',
        ]);
        $draw = $group->draws()->create(['drawn_at' => now(), 'status' => 'completed']);
        $draw->assignments()->create(['giver_id' => $member->id, 'receiver_id' => $receiver->id]);
        $code = app(ParticipantInvitation::class)->issue($member);

        Auth::logout();
        $this->post(route('pwa.participant.verify'), ['code' => $code])->assertRedirect(route('pwa.participant'));
        $this->get(route('pwa.participant'))->assertOk()->assertSee('Dashboard do grupo')->assertSee('Sorteio');
        $this->get(route('home'))->assertOk()->assertSee('Abrir meu grupo');
        $this->get(route('amigo.dashboard', $group))->assertOk()->assertHeader('Cache-Control', 'no-store, private')
            ->assertSee('Participantes do grupo')->assertSee($receiver->name)->assertDontSee('reservado@example.test')
            ->assertDontSee('Preferência privada')->assertDontSee('Sortear quem começa');
        $this->get(route('draw.show', $group))->assertOk()->assertDontSee('Gerenciar convite');
        $this->get(route('reveal.list', $group))->assertOk();
        $collective = route('dashboard.revealed-friends', $group);
        $this->get($collective)->assertRedirect(route('draw.show', $group));
        $this->travel(1)->hour();
        $this->get($collective)->assertOk()->assertSee('Quem foi tirado')->assertSee($receiver->name);

        foreach (['amigo.dashboard', 'draw.show', 'reveal.list', 'dashboard.revealed-friends'] as $route) {
            $this->get(route($route, $otherGroup))->assertForbidden()->assertDontSee($otherGroup->name);
        }

        $this->get(route('groups.edit', $group))->assertRedirect(route('login'));
        $this->postJson(route('draw.store', $group))->assertUnauthorized();
        $this->postJson(route('participants.store', $group), ['name' => 'Terceira pessoa'])->assertUnauthorized();
        $this->postJson(route('participants.invite.store', [$group, $receiver]))->assertUnauthorized();
        app(ParticipantInvitation::class)->revoke($member);
        $this->get(route('amigo.dashboard', $group))->assertRedirect(route('pwa.participant'));
    }

    public function test_email_recovery_renews_an_expired_invite_and_is_single_use(): void
    {
        Mail::fake();
        $this->signInOrganizer();
        $group = $this->ownedGroup(['name' => 'Meu grupo']);
        $member = $group->participants()->create(['name' => 'Convidada', 'email' => 'Pessoa@example.test']);
        $other = $group->participants()->create(['name' => 'Outro nome', 'email' => 'outra@example.test']);
        $invitations = app(ParticipantInvitation::class);
        $old = $invitations->issue($member);
        $invitations->issue($other);
        $member->forceFill(['invite_expires_at' => now()->subHour()])->save();
        Auth::logout();

        $portal = route('pwa.participant');
        $recovery = route('pwa.participant.recover');
        $verify = route('pwa.participant.verify');
        $this->post($recovery, ['email' => 'desconhecido@example.test'])->assertRedirect($portal)->assertSessionHas('recovery_status');
        Mail::assertNothingSent();

        $this->post($recovery, ['email' => 'pessoa@example.test'])->assertRedirect($portal)->assertSessionHas('recovery_status');
        Mail::assertSentCount(1);
        $mail = Mail::sent(ParticipantAccessRecovery::class)->sole();
        $this->assertSame(['Convidada · Meu grupo'], array_column($mail->links, 'label'));
        $link = $mail->links[0]['url'];
        $mail->assertSeeInHtml('Convidada');
        $mail->assertDontSeeInHtml('Outro nome');
        $token = substr($link, strlen($portal.'#convite='));
        $this->assertSame(hash('sha256', $token), $member->fresh()->invite_recovery_hash);

        $this->post($verify, ['code' => $token])->assertRedirect($portal);
        $this->get($portal)->assertSee('Convidada');
        $this->post(route('pwa.participant.logout'));
        $this->from($portal)->post($verify, ['code' => $token])->assertSessionHasErrors('code');
        $this->from($portal)->post($verify, ['code' => $old])->assertSessionHasErrors('code');

        $this->post($recovery, ['email' => 'pessoa@example.test'])->assertRedirect($portal);
        $latestLink = Mail::sent(ParticipantAccessRecovery::class)->last()->links[0]['url'];
        $invitations->revoke($member);
        $this->from($portal)->post($verify, ['code' => substr($latestLink, strlen($portal.'#convite='))])->assertSessionHasErrors('code');
    }
}
