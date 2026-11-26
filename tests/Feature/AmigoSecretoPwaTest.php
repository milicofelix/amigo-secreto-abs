<?php

namespace Tests\Feature;

use App\Jobs\SendDrawWebPush;
use App\Models\AmigoSecreto\Group;
use App\Models\AmigoSecreto\Participant;
use App\Models\AmigoSecreto\ParticipantPushSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Minishlink\WebPush\MessageSentReport;
use Minishlink\WebPush\WebPush;
use Mockery;
use Tests\TestCase;

class AmigoSecretoPwaTest extends TestCase
{
    use RefreshDatabase;

    private const CODE = 'private-test-code-123456';

    public function test_private_access_is_required_for_status_and_retries_preserve_the_first_timestamp(): void
    {
        [$group, $participant, $other] = $this->group();
        $draw = $group->draws()->create(['drawn_at' => now(), 'status' => 'completed']);
        $draw->assignments()->create(['giver_id' => $participant->id, 'receiver_id' => $other->id]);
        $status = ['participant_id' => $participant->id, 'draw_id' => $draw->id, 'field' => 'bought_gift', 'value' => 1];

        $this->get(route('reveal.result', $participant))->assertRedirect(route('pwa.participant'));
        $this->get(route('pwa.participant'))->assertOk()->assertDontSee($other->name);
        $this->postJson(route('participant.status.update'), $status)->assertUnauthorized();
        $this->post(route('pwa.participant.verify'), ['code' => self::CODE])->assertRedirect(route('pwa.participant'));
        $this->get(route('pwa.participant'))->assertOk()->assertSee($other->name)->assertHeader('Cache-Control', 'no-store, private');
        $this->postJson(route('participant.status.update'), ['participant_id' => $other->id] + $status)->assertForbidden();
        $this->postJson(route('participant.status.update'), ['field' => 'email'] + $status)->assertUnprocessable();
        $this->postJson(route('participant.status.update'), $status)->assertNoContent();
        $confirmedAt = $participant->fresh()->bought_gift_at;
        $this->assertNotNull($confirmedAt);
        $this->travel(1)->hour();
        $this->postJson(route('participant.status.update'), $status)->assertNoContent();
        $this->assertSame($confirmedAt, $participant->fresh()->bought_gift_at);
        $participant->forceFill(['pwa_access_hash' => hash('sha256', 'rotated')])->save();
        $this->postJson(route('participant.status.update'), $status)->assertUnauthorized();
    }

    public function test_push_requires_verified_access_and_only_queues_after_a_new_draw(): void
    {
        [$group, $participant] = $this->group();
        $this->enablePush();
        Queue::fake();
        $subscription = $this->subscription('test');

        $this->postJson(route('pwa.push.subscribe'), $subscription)->assertUnauthorized();
        $this->post(route('pwa.participant.verify'), ['code' => self::CODE])->assertRedirect();
        $this->postJson(route('pwa.push.subscribe'), ['endpoint' => 'https://example.test/'] + $subscription)
            ->assertUnprocessable();
        $this->postJson(route('pwa.push.subscribe'), $subscription)->assertNoContent();
        $this->postJson(route('pwa.push.subscribe'), $subscription)->assertNoContent();
        $this->assertDatabaseCount('participant_push_subscriptions', 1);
        $this->assertSame($participant->id, ParticipantPushSubscription::first()->participant_id);

        $organizer = User::factory()->organizer()->create();
        $group->user()->associate($organizer)->save();
        $this->actingAs($organizer)->post(route('draw.store', $group))->assertRedirect();
        $draw = $group->draws()->sole();
        $this->assertDatabaseCount('assignments', 2);
        $this->assertSame('completed', $draw->status);
        $this->post(route('draw.store', $group))->assertSessionHas('error');
        Queue::assertPushed(SendDrawWebPush::class, fn ($job) => $job->drawId === $draw->id);
        Queue::assertPushed(SendDrawWebPush::class, 1);

        $this->deleteJson(route('pwa.push.unsubscribe'), $subscription)->assertNoContent();
        $this->assertDatabaseCount('participant_push_subscriptions', 0);
    }

    public function test_push_prunes_expired_devices_and_skips_delivered_subscriptions(): void
    {
        [$group, $participant, $other] = $this->group();
        $this->enablePush();
        $draw = $group->draws()->create(['drawn_at' => now(), 'status' => 'completed']);
        $draw->assignments()->create(['giver_id' => $participant->id, 'receiver_id' => $other->id]);
        foreach (['valid', 'expired'] as $device) {
            $subscription = $this->subscription($device);
            ParticipantPushSubscription::create([
                'participant_id' => $participant->id,
                'endpoint_hash' => hash('sha256', $subscription['endpoint']),
                'subscription' => $subscription,
            ]);
        }

        $valid = Mockery::mock(MessageSentReport::class);
        $valid->shouldReceive('isSubscriptionExpired')->andReturnFalse();
        $valid->shouldReceive('isSuccess')->andReturnTrue();
        $expired = Mockery::mock(MessageSentReport::class);
        $expired->shouldReceive('isSubscriptionExpired')->andReturnTrue();
        $push = Mockery::mock(WebPush::class);
        $push->shouldReceive('sendOneNotification')->twice()->withArgs(
            fn ($subscription, $payload) => json_decode($payload, true) === ['tag' => 'sorteio-'.$draw->id]
        )->andReturn($valid, $expired);
        $job = Mockery::mock(SendDrawWebPush::class, [$draw->id])->makePartial()->shouldAllowMockingProtectedMethods();
        $job->shouldReceive('webPush')->andReturn($push);
        $job->handle();
        $job->handle();

        $this->assertDatabaseCount('participant_push_subscriptions', 1);
        $this->assertSame($draw->id, ParticipantPushSubscription::first()->last_notified_draw_id);
        $this->assertSame(['push_sent', 'push_expired'], $draw->events()->orderBy('id')->pluck('type')->all());
    }

    private function group(): array
    {
        $group = Group::create(['name' => 'Grupo de teste', 'event_date' => now()->addWeek()->toDateString()]);
        $participant = $group->participants()->create(['name' => 'Participante A']);
        $participant->forceFill(['pwa_access_hash' => hash('sha256', self::CODE), 'invite_expires_at' => now()->addMonth()])->save();
        $other = $group->participants()->create(['name' => 'Participante B']);

        return [$group, $participant, $other];
    }

    private function enablePush(): void
    {
        config(['services.web_push' => ['subject' => 'mailto:test@example.test', 'public_key' => 'public', 'private_key' => 'private']]);
    }

    private function subscription(string $device): array
    {
        return [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/'.$device,
            'keys' => ['p256dh' => str_repeat('a', 87), 'auth' => str_repeat('b', 22)],
        ];
    }
}
