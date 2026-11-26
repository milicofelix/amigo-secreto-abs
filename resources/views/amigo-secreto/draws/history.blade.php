<section class="mt-6 rounded bg-white p-4 shadow">
    <h2 class="mb-3 text-xl font-semibold">Histórico do sorteio</h2>
    <ol class="divide-y">
        @forelse ($history as $event)
            <li class="py-2">
                <time datetime="{{ $event->created_at->toIso8601String() }}" class="text-sm text-gray-600">
                    {{ $event->created_at->format('d/m/Y H:i') }}
                </time>
                <span class="ml-2 font-semibold">Sorteio #{{ $event->draw_id }} · {{ match ($event->type) {
                    'confirmed' => 'Sorteio confirmado',
                    'cancelled' => 'Sorteio cancelado',
                    'email_sent' => 'Aviso por e-mail processado',
                    'email_failed' => 'Falha no aviso por e-mail',
                    'push_sent' => 'Aviso push aceito pelo serviço',
                    'push_failed' => 'Falha no aviso push',
                    'push_expired' => 'Inscrição de push expirada',
                    'notification_dispatch_failed' => 'Falha ao iniciar aviso por '.($event->details['channel'] === 'email' ? 'e-mail' : 'push'),
                    default => 'Evento de sorteio',
                } }}</span>
                @if ($event->actor)
                    <span class="text-sm text-gray-600">({{ $event->actor->name }})</span>
                @endif
                @if ($event->type === 'cancelled')
                    <p class="mt-1 whitespace-pre-wrap break-words text-gray-700">{{ $event->details['reason'] }}</p>
                @endif
            </li>
        @empty
            <li class="text-gray-600">Nenhum evento registrado.</li>
        @endforelse
    </ol>
    <div class="mt-3">{{ $history->links() }}</div>
</section>
