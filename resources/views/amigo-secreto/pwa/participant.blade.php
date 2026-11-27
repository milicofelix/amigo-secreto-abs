@extends('layouts.amigo-secreto-private')

@section('title', 'Meu Amigo Secreto')

@push('head')
    <script src="{{ asset('pwa-participant.js') }}?v=6" defer></script>
@endpush

@section('content')
<section class="card">
    <h1>Meu Amigo Secreto</h1>

    @php($showEntry = !$participant || $errors->hasAny(['code', 'email']) || session('invite_error') || session('recovery_status'))
    <div id="participant-entry" @if (!$showEntry) hidden @endif>
        <p>Use o convite privado que você recebeu do organizador.</p>
        @if (session('invite_error'))
            <p class="error" role="alert">{{ session('invite_error') }}</p>
        @endif
        <form method="POST" action="{{ route('pwa.participant.verify', absolute: false) }}">
            @csrf
            <label for="participant-code">Código privado</label>
            <input id="participant-code" name="code" type="password" autocomplete="off" required maxlength="48">
            @error('code')
                <p class="error" role="alert">{{ $message }}</p>
            @enderror
            <button>Entrar</button>
        </form>
        @if (session('recovery_status'))
            <p class="notice" role="status">{{ session('recovery_status') }}</p>
        @endif
        <details class="section" @if ($errors->has('email')) open @endif>
            <summary>Perdi meu convite</summary>
            <p>Informe o e-mail cadastrado pelo organizador para receber um link de acesso. Se você não tiver um e-mail cadastrado, peça ao organizador que reenvie o convite.</p>
            <form method="POST" action="{{ route('pwa.participant.recover', absolute: false) }}">
                @csrf
                <label for="recovery-email">E-mail cadastrado</label>
                <input id="recovery-email" name="email" type="email" autocomplete="email" required maxlength="255">
                @error('email')
                    <p class="error" role="alert">{{ $message }}</p>
                @enderror
                <button class="secondary">Enviar link de acesso</button>
            </form>
        </details>
    </div>
    @if ($participant)
        <div id="participant-session" @if ($showEntry) hidden @endif>
        <div id="pwa-participant" data-participant="{{ $participant->id }}" data-csrf="{{ csrf_token() }}"
             data-draw-id="{{ $assignment?->draw_id }}"
             data-invite-scope="{{ $participant->inviteScope() }}"
             data-status-url="{{ route('participant.status.update', absolute: false) }}"
             data-push-url="{{ route('pwa.push.subscribe', absolute: false) }}"
             data-push-key="{{ \App\Jobs\SendDrawWebPush::enabled() ? config('services.web_push.public_key') : '' }}">
            <p><strong>{{ $participant->name }}</strong> · {{ $participant->group?->name }}</p>
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
