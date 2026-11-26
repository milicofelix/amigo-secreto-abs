@extends('layouts.amigo-secreto-private')

@section('title', 'Convite do participante')

@section('content')
<section class="card">
    <h1>Convite de {{ $participant->name }}</h1>
    <p>{{ $group->name }}</p>

    @if (session('invite_status'))
        <p class="notice" role="status">{{ session('invite_status') }}</p>
    @endif

    @if ($participant->hasActiveInvite())
        <p>Convite ativo até <time datetime="{{ $participant->invite_expires_at->toIso8601String() }}">{{ $participant->invite_expires_at->format('d/m/Y H:i') }}</time>.</p>
    @elseif ($participant->invite_expires_at)
        <p>Convite vencido.</p>
    @else
        <p>Sem convite ativo.</p>
    @endif

    @isset($qr)
        <div class="section">
            <p>Envie o link, o QR ou o código somente para {{ $participant->name }}. Eles são exibidos apenas nesta emissão.</p>
            <label for="invite-link">Link privado</label>
            <input id="invite-link" type="text" readonly value="{{ $url }}" spellcheck="false">
            <label for="invite-code">Código para digitar no portal</label>
            <input id="invite-code" type="text" readonly value="{{ $code }}" spellcheck="false">
            <img class="qr" src="{{ $qr }}" alt="QR do convite">
            <a class="button secondary" href="{{ $qr }}" download="convite-amigo-secreto.png">Baixar QR</a>
        </div>
    @endisset

    @if ($participant->hasActiveInvite())
        <p class="notice">Substituir ou revogar o convite encerra o acesso concedido pelo código anterior.</p>
    @endif

    <div class="actions">
        <form method="POST" action="{{ route('participants.invite.store', [$group, $participant], absolute: false) }}"
              @if($participant->hasActiveInvite()) onsubmit="return confirm('Substituir este convite e encerrar o acesso anterior?')" @endif>
            @csrf
            <button>{{ $participant->hasActiveInvite() ? 'Substituir convite' : 'Gerar convite' }}</button>
        </form>
        @if ($participant->hasActiveInvite())
            <form method="POST" action="{{ route('participants.invite.destroy', [$group, $participant], absolute: false) }}"
                  onsubmit="return confirm('Revogar o acesso deste participante?')">
                @csrf
                @method('DELETE')
                <button class="danger">Revogar convite</button>
            </form>
        @endif
    </div>

    <a href="{{ route('groups.edit', $group, absolute: false) }}">← Voltar ao grupo</a>
</section>
@endsection
