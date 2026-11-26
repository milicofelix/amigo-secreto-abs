<?php

namespace App\Jobs;

use App\Models\AmigoSecreto\AsDraw;
use App\Models\AmigoSecreto\ParticipantPushSubscription;
use GuzzleHttp\Client;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class SendDrawWebPush implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $uniqueFor = 3600;
    public int $tries = 3;
    public array $backoff = [30, 120];

    public function __construct(public int $drawId) {}

    public function uniqueId(): string
    {
        return (string) $this->drawId;
    }

    public static function enabled(): bool
    {
        return (bool) (config('services.web_push.subject')
            && config('services.web_push.public_key') && config('services.web_push.private_key'));
    }

    public function handle(): void
    {
        $draw = AsDraw::find($this->drawId);
        if (!self::enabled() || $draw?->status !== 'completed') {
            return;
        }

        $subscriptions = ParticipantPushSubscription::whereIn('participant_id', $draw->assignments()->select('giver_id'))
            ->where(fn ($query) => $query->whereNull('last_notified_draw_id')
                ->orWhere('last_notified_draw_id', '<', $draw->id));
        $push = $this->webPush();
        $retry = false;

        foreach ($subscriptions->lazyById() as $record) {
            try {
                $report = $push->sendOneNotification(
                    Subscription::create($record->subscription + ['contentEncoding' => 'aes128gcm']),
                    json_encode(['tag' => 'sorteio-'.$draw->id], JSON_THROW_ON_ERROR),
                );
                if ($report->isSubscriptionExpired()) {
                    $record->delete();
                } elseif ($report->isSuccess()) {
                    $record->update(['last_notified_draw_id' => $draw->id]);
                } else {
                    Log::warning('Falha de entrega Web Push.', ['draw_id' => $draw->id]);
                    $status = $report->getResponse()?->getStatusCode();
                    $retry = $retry || $status === null || $status === 429 || $status >= 500;
                }
            } catch (\Throwable) {
                Log::warning('Erro de entrega Web Push.', ['draw_id' => $draw->id]);
            }
        }

        if ($retry) {
            throw new \RuntimeException('Falha temporária de entrega Web Push.');
        }
    }

    protected function webPush(): WebPush
    {
        return new WebPush(['VAPID' => [
            'subject' => config('services.web_push.subject'),
            'publicKey' => config('services.web_push.public_key'),
            'privateKey' => config('services.web_push.private_key'),
        ]], ['TTL' => 3600], client: new Client(['timeout' => 5, 'connect_timeout' => 3, 'allow_redirects' => false]));
    }
}
