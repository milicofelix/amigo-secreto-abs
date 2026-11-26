<?php

namespace Tests\Feature;

use App\Mail\SecretFriendNotification;
use App\Models\AmigoSecreto\ParticipantPushSubscription;
use App\Models\User;
use App\Services\ParticipantInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\HasOrganizerGroups;
use Tests\TestCase;

class AmigoSecretoInvitationTest extends TestCase
{
    use HasOrganizerGroups, RefreshDatabase;

    public function test_owner_can_issue_and_revoke_a_private_invite_and_legacy_links_only_redirect(): void
    {
        $owner = $this->signInOrganizer();
        $group = $this->ownedGroup(['name' => 'Grupo de teste']);
        $giver = $group->participants()->create(['name' => 'Pessoa convidada', 'secret_key' => 'LEGADO']);
        $receiver = $group->participants()->create([
            'name' => 'Destinatário esperado', 'wishlist' => 'Livro de receitas',
            'email' => 'privado@example.test', 'whatsapp' => '5511999999999',
        ]);
        $third = $group->participants()->create(['name' => 'Outro participante']);
        $draw = $group->draws()->create(['drawn_at' => now(), 'status' => 'completed']);
        $draw->assignments()->create(['giver_id' => $giver->id, 'receiver_id' => $receiver->id]);
        $draw->assignments()->create(['giver_id' => $receiver->id, 'receiver_id' => $third->id]);

        $issue = route('participants.invite.store', [$group, $giver]);
        $revoke = route('participants.invite.destroy', [$group, $giver]);
        $portal = route('pwa.participant');
        $verify = route('pwa.participant.verify');

        $this->actingAs(User::factory()->organizer()->create());
        $this->post($issue)->assertForbidden();
        $this->delete($revoke)->assertForbidden();

        $this->actingAs($owner);
        $issued = $this->post($issue)->assertOk()->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertSee('data:image/png;base64,', false)->assertDontSee('LEGADO');
        $code = $issued->viewData('code');
        $this->assertSame($portal.'#convite='.$code, $issued->viewData('url'));
        $this->assertSame(hash('sha256', $code), $giver->fresh()->pwa_access_hash);
        $this->get(route('participants.qr', [$group, $giver]))->assertOk()->assertDontSee($code);

        $this->get(route('reveal.by.key', 'LEGADO'))->assertRedirect($portal);
        $this->get($portal)->assertDontSee('Destinatário esperado');
        $this->post($verify, ['code' => $code])->assertRedirect($portal);
        $this->get($portal)->assertOk()->assertSee('Destinatário esperado')->assertSee('Livro de receitas')
            ->assertDontSee($third->name)->assertDontSee('privado@example.test')->assertDontSee('5511999999999');
        $this->assertNotNull($giver->fresh()->first_revealed_at);

        $this->delete($revoke)->assertRedirect(route('participants.qr', [$group, $giver]));
        $this->get($portal)->assertOk()->assertDontSee('Destinatário esperado');
        $this->from($portal)->post($verify, ['code' => $code])->assertSessionHasErrors('code');
        $this->assertNull(session()->getOldInput('code'));
    }

    public function test_expiration_and_replacement_invalidate_sessions_and_preserve_preferences(): void
    {
        $this->signInOrganizer();
        $participant = $this->ownedGroup(['name' => 'Grupo de teste'])->participants()->create(['name' => 'Participante', 'email' => 'teste@example.test']);
        $invitations = app(ParticipantInvitation::class);
        $code = $invitations->issue($participant);
        $portal = route('pwa.participant');

        $this->post(route('pwa.participant.verify'), ['code' => $code])->assertRedirect($portal);
        $this->patch(route('pwa.participant.email'), ['enabled' => '0'])->assertRedirect($portal);
        ParticipantPushSubscription::create([
            'participant_id' => $participant->id, 'endpoint_hash' => str_repeat('a', 64),
            'subscription' => ['endpoint' => 'https://fcm.googleapis.com/test'],
        ]);
        $replacement = $invitations->issue($participant, replace: true);
        $this->get($portal)->assertDontSee('Ativar avisos por e-mail');
        $this->post(route('pwa.participant.verify'), ['code' => $replacement])->assertRedirect($portal);
        $this->get($portal)->assertSee('Ativar avisos por e-mail');
        $this->assertDatabaseCount('participant_push_subscriptions', 1);

        $this->travel(30)->days();
        $this->get($portal)->assertDontSee('Ativar avisos por e-mail');
        $this->from($portal)->post(route('pwa.participant.verify'), ['code' => $replacement])->assertSessionHasErrors('code');
    }

    public function test_draw_only_queues_generic_email_for_subscribed_recipients(): void
    {
        Mail::fake();
        $this->signInOrganizer();
        $group = $this->ownedGroup(['name' => 'Grupo reservado']);
        $group->participants()->create(['name' => 'Confirmado', 'email' => 'sim@example.test']);
        $other = $group->participants()->create(['name' => 'Nome secreto', 'email' => 'nao@example.test', 'wishlist' => 'Desejo secreto']);
        $other->forceFill(['draw_email_enabled' => false])->save();

        $this->post(route('draw.store', $group))->assertRedirect();
        Mail::assertQueuedCount(1);
        Mail::assertQueued(SecretFriendNotification::class, function ($mail) {
            $mail->assertSeeInHtml(route('pwa.participant'));
            $mail->assertDontSeeInHtml('Nome secreto');
            $mail->assertDontSeeInHtml('Grupo reservado');
            $mail->assertDontSeeInHtml('Desejo secreto');

            return $mail->hasTo('sim@example.test');
        });
    }

    public function test_existing_photos_are_only_delivered_through_an_owner_route(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Storage::disk('public')->put('participants/legacy.jpg', 'image bytes');
        $owner = $this->signInOrganizer();
        $group = $this->ownedGroup(['name' => 'Grupo de teste']);
        $participant = $group->participants()->create(['name' => 'Participante', 'photo' => 'participants/legacy.jpg']);
        $photo = route('participants.photo', [$group, $participant]);

        $this->actingAs(User::factory()->organizer()->create())->get($photo)->assertForbidden();
        $this->actingAs($owner)->get($photo)
            ->assertOk()->assertHeader('Cache-Control', 'no-store, private')
            ->assertStreamedContent('image bytes');
    }
}
