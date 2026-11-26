@extends('layouts.amigo-secreto-private')

@section('title', 'Meu Amigo Secreto')

@push('head')
    <script src="{{ asset('pwa-participant.js') }}?v=3" defer></script>
@endpush

@section('content')
<section class="card">
    <h1>Meu Amigo Secreto</h1>

    <div id="participant-entry" @if ($participant && !$errors->has('code') && !session('invite_error')) hidden @endif>
        <p>Use o convite privado que você recebeu do organizador.</p>
        @if (session('invite_error'))
            <p class="error" role="alert">{{ session('invite_error') }}</p>
        @endif
        <form method="POST" action="{{ route('pwa.participant.verify', absolute: false) }}">
            @csrf
            <label for="participant-code">Código privado</label>
            <input id="participant-code" name="code" type="password" autocomplete="off" required maxlength="24">
            @error('code')
                <p class="error" role="alert">{{ $message }}</p>
            @enderror
            <button>Entrar</button>
        </form>
    </div>
    @if ($participant)
        <div id="participant-session" @if ($errors->has('code') || session('invite_error')) hidden @endif>
        <div id="pwa-participant" data-participant="{{ $participant->id }}" data-csrf="{{ csrf_token() }}"
             data-invite-scope="{{ hash_hmac('sha256', $participant->pwa_access_hash, config('app.key')) }}"
             data-status-url="{{ route('participant.status.update', absolute: false) }}"
             data-push-url="{{ route('pwa.push.subscribe', absolute: false) }}"
             data-push-key="{{ \App\Jobs\SendDrawWebPush::enabled() ? config('services.web_push.public_key') : '' }}">
            <p><strong>{{ $participant->name }}</strong></p>
            @if ($assignment?->receiver)
                <div class="result">
                    <p>Você tirou <strong>{{ $assignment->receiver->name }}</strong>.</p>
                    @if ($assignment->receiver->wishlist)
                        <p>Sugestão de presente: {{ $assignment->receiver->wishlist }}</p>
                    @endif
                </div>
                <div class="actions">
                    <button type="button" class="secondary" data-gift-status="shared_gift_idea" @disabled($participant->shared_gift_idea)>
                        {{ $participant->shared_gift_idea ? 'Ideia de presente compartilhada' : 'Marcar ideia como compartilhada' }}
                    </button>
                    <button type="button" id="pwa-gift" data-recorded="{{ $participant->bought_gift ? 'yes' : 'no' }}"
                            @disabled($participant->bought_gift)>
                        {{ $participant->bought_gift ? 'Presente marcado como comprado' : 'Marcar presente como comprado' }}
                    </button>
                </div>
                <p id="pwa-sync-status" class="notice" role="status"></p>
                <button type="button" id="pwa-sync-retry" class="secondary" hidden>Tentar sincronizar</button>
                <a id="pwa-sync-login" href="{{ route('pwa.participant', absolute: false) }}" hidden>Verificar acesso novamente</a>
            @else
                <p>O sorteio ainda não foi realizado.</p>
            @endif

            <section class="section" aria-label="Notificações">
                @if (session('notification_status'))
                    <p class="notice" role="status">{{ session('notification_status') }}</p>
                @endif
                <div class="actions">
                    @if ($participant->email)
                        <form method="POST" action="{{ route('pwa.participant.email', absolute: false) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="enabled" value="{{ $participant->draw_email_enabled ? '0' : '1' }}">
                            <button class="secondary">{{ $participant->draw_email_enabled ? 'Desativar avisos por e-mail' : 'Ativar avisos por e-mail' }}</button>
                        </form>
                    @endif
                    @if (\App\Jobs\SendDrawWebPush::enabled())
                        <button type="button" id="pwa-push-toggle" class="secondary" disabled>Ativar notificações</button>
                    @endif
                </div>
                @if (\App\Jobs\SendDrawWebPush::enabled())
                    <p id="pwa-push-status" class="notice" role="status"></p>
                @endif
            </section>
        </div>
        <form method="POST" action="{{ route('pwa.participant.logout', absolute: false) }}" class="section">
            @csrf
            <button class="secondary">Sair deste acesso</button>
        </form>
        </div>
    @endif
</section>
@endsection
