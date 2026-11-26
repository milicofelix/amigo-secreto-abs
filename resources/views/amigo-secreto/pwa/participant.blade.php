@extends('layouts.amigo-secreto')

@section('title', 'Meu Amigo Secreto')

@section('content')
<section class="mx-auto max-w-lg rounded-xl bg-white p-6 shadow">
    <h1 class="mb-4 text-2xl font-bold text-indigo-700">Meu Amigo Secreto</h1>

    @if (!$participant)
        <p class="mb-4 text-gray-700">Use o código privado que o organizador entregou para você.</p>
        <form method="POST" action="{{ route('pwa.participant.verify') }}">
            @csrf
            <label for="participant-code" class="font-semibold">Código privado</label>
            <input id="participant-code" name="code" type="password" autocomplete="off" required
                   maxlength="24" class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
            @error('code')
                <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
            @enderror
            <button class="mt-4 rounded bg-indigo-600 px-4 py-2 font-semibold text-white">Entrar</button>
        </form>
    @else
        <div id="pwa-participant" data-participant="{{ $participant->id }}" data-csrf="{{ csrf_token() }}"
             data-status-url="{{ route('participant.status.update', absolute: false) }}"
             data-push-url="{{ route('pwa.push.subscribe', absolute: false) }}"
             data-push-key="{{ \App\Jobs\SendDrawWebPush::enabled() ? config('services.web_push.public_key') : '' }}">
            <p class="mb-4 font-semibold">{{ $participant->name }}</p>
            @if ($assignment?->receiver)
                <p class="mb-2">Você tirou <strong>{{ $assignment->receiver->name }}</strong>.</p>
                @if ($assignment->receiver->wishlist)
                    <p class="mb-4 text-gray-700">Sugestão: {{ $assignment->receiver->wishlist }}</p>
                @endif
                <div class="my-4 space-y-3">
                    <button type="button" data-gift-status="shared_gift_idea" @disabled($participant->shared_gift_idea)
                            class="rounded border border-indigo-600 px-3 py-2 text-indigo-700 disabled:opacity-60">
                        {{ $participant->shared_gift_idea ? 'Ideia de presente compartilhada' : 'Marcar ideia como compartilhada' }}
                    </button>
                    <button type="button" id="pwa-gift" data-recorded="{{ $participant->bought_gift ? 'yes' : 'no' }}"
                            @disabled($participant->bought_gift)
                            class="rounded bg-indigo-600 px-3 py-2 text-white disabled:opacity-60">
                        {{ $participant->bought_gift ? 'Presente marcado como comprado' : 'Marcar presente como comprado' }}
                    </button>
                    <p id="pwa-sync-status" class="text-sm text-gray-700" role="status"></p>
                    <button type="button" id="pwa-sync-retry" class="text-sm font-semibold text-indigo-700 underline" hidden>
                        Tentar sincronizar
                    </button>
                    <a id="pwa-sync-login" href="{{ route('pwa.participant') }}" class="text-sm font-semibold text-indigo-700 underline" hidden>
                        Verificar acesso novamente
                    </a>
                </div>
            @else
                <p class="mb-4 text-gray-700">O sorteio ainda não foi realizado.</p>
            @endif

            @if (\App\Jobs\SendDrawWebPush::enabled())
                <section class="mt-5 border-t pt-4" aria-label="Notificações">
                    <button type="button" id="pwa-push-toggle" disabled
                            class="rounded border border-indigo-600 px-3 py-2 text-indigo-700 disabled:opacity-60">Ativar notificações</button>
                    <p id="pwa-push-status" class="mt-2 text-sm text-gray-700" role="status"></p>
                </section>
            @endif
        </div>
        <form method="POST" action="{{ route('pwa.participant.logout') }}" class="mt-5">
            @csrf
            <button class="text-sm text-gray-700 underline">Sair deste acesso</button>
        </form>
        <script src="{{ asset('pwa-participant.js') }}" defer></script>
    @endif
</section>
@endsection
