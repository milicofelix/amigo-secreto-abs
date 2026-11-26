@extends('layouts.rifa')

@section('content')

{{-- ========================================================= --}}
{{--   ESTILOS DO CARD DO VENCEDOR                            --}}
{{-- ========================================================= --}}
<style>
    .winner-card {
        background: linear-gradient(135deg, #fff7d1, #ffe8a3);
        border: 3px solid #f1c40f;
        border-radius: 14px;
        padding: 25px;
        text-align: center;
        box-shadow: 0 0 25px rgba(241, 196, 15, 0.6);
        animation: pulseGlow 1.8s infinite alternate;
    }

    @keyframes pulseGlow {
        from { box-shadow: 0 0 15px rgba(241, 196, 15, 0.4); }
        to   { box-shadow: 0 0 35px rgba(241, 196, 15, 0.9); }
    }

    .winner-icon {
        font-size: 65px;
        margin-bottom: 15px;
        animation: pop 0.7s ease-out;
    }

    @keyframes pop {
        0% { transform: scale(0.4); opacity: 0; }
        70% { transform: scale(1.15); opacity: 1; }
        100% { transform: scale(1); }
    }

    .winner-name {
        font-size: 28px;
        font-weight: bold;
        color: #c0392b;
    }

    .winner-sub {
        font-size: 18px;
        color: #7f8c8d;
        font-weight: 500;
    }

    .winner-card-inline {
        border: 3px solid #f1c40f !important;
        box-shadow: 0 0 18px rgba(241, 196, 15, 0.65);
        position: relative;
        animation: winnerPulse 1.5s infinite alternate;
    }

    @keyframes winnerPulse {
        from { box-shadow: 0 0 10px rgba(241, 196, 15, 0.4); }
        to   { box-shadow: 0 0 25px rgba(241, 196, 15, 1); }
    }

    .winner-badge-icon {
        position: absolute;
        top: -12px;
        right: -12px;
        background: #f1c40f;
        color: #fff;
        font-size: 22px;
        font-weight: bold;
        padding: 6px 10px;
        border-radius: 50%;
        box-shadow: 0 0 10px rgba(0,0,0,0.3);
    }

</style>


{{-- ========================================================= --}}
{{--   IDENTIFICAR NOME SORTEADO NO BANCO                     --}}
{{-- ========================================================= --}}
@php
    $nomeSorteado = null;

    if ($rifa->sorteado_nome_id) {
        $nomeSorteado = \App\Models\Rifas\RifaNome::find($rifa->sorteado_nome_id);
    }

    $existeVencedor =
        $nomeSorteado &&
        $nomeSorteado->status === 'pago' &&
        !empty($nomeSorteado->comprador_nome);
@endphp


{{-- ========================================================= --}}
{{--   CARD DESTACANDO O GANHADOR                             --}}
{{-- ========================================================= --}}
@if($nomeSorteado)
    <div class="winner-card mb-4">

        <div class="winner-icon">🏆</div>

        <h2 class="winner-name">
            {{ $nomeSorteado->nome }}
        </h2>

        <p class="winner-sub">
            @if($existeVencedor)
                <strong>{{ $nomeSorteado->comprador_nome }}</strong><br>
                <small>{{ $nomeSorteado->comprador_whatsapp }}</small>
            @else
                <span class="text-danger fw-bold">Nenhum vencedor</span><br>
                <small>(Nome não estava pago)</small>
            @endif
        </p>
    </div>
@endif



{{-- ========================================================= --}}
{{--   CABEÇALHO DA CARTELA                                   --}}
{{-- ========================================================= --}}
<h1 class="mb-3">{{ $rifa->titulo }}</h1>
<p><strong>Prêmio:</strong> {{ $rifa->premio }}</p>
<p><strong>Valor por nome:</strong> R$ {{ number_format($rifa->valor,2,',','.') }}</p>


{{-- BOTÃO PARA SORTEAR (só aparece se ainda não sorteada) --}}
@if(!$rifa->sorteado_em)
    <a href="{{ route('admin.rifas.sorteio.animado', $rifa->id) }}"
        class="btn btn-danger mb-3">
        🎡 Sortear Ganhador (Roleta)
    </a>
@endif
<a href="{{ route('rifas.create') }}" class="btn btn-primary mb-3">
    Nova Rifa
</a>
<a href="{{ route('rifas.gerarNomes.form', $rifa->id) }}"
   class="btn btn-success mb-3">
    ➕ Gerar nomes para esta rifa
</a>

<hr>


{{-- ========================================================= --}}
{{--   LISTAGEM DAS CARTELAS                                  --}}
{{-- ========================================================= --}}
<div class="row row-cols-2 row-cols-md-4 row-cols-lg-5 g-3">
    @foreach($rifa->nomes as $nome)

        @php
            $cor = match($nome->status) {
                'disponivel' => '#e5ffe5',
                'reservado'  => '#fff3cd',
                'pago'       => '#fecaca',
                default      => '#ffffff',
            };
        @endphp

        <div class="col">
            <div class="p-3 rounded border card-hover 
                @if($nomeSorteado && $nome->id === $nomeSorteado->id) winner-card-inline @endif"
                    style="background: {{ $cor }}; position:relative;">

                @if($nomeSorteado && $nome->id === $nomeSorteado->id)
                    <div class="winner-badge-icon">🏆</div>
                @endif

                <strong class="d-block text-center">{{ $nome->nome }}</strong>

                {{-- NÃO DISPONÍVEL --}}
                @if($nome->status !== 'disponivel')

                    <small class="d-block mt-2 text-center">
                        Comprado por:<br>
                        <strong>{{ $nome->comprador_nome }}</strong><br>
                        <small>{{ $nome->comprador_whatsapp }}</small><br>

                        @if($nome->status === 'reservado')
                            <span class="badge bg-warning text-dark mt-2">Aguardando pagamento</span>
                            <a href="{{ route('rifas.pagar', $nome->id) }}" 
                               class="btn btn-sm btn-primary w-100 mt-2">
                                Pagar agora
                            </a>
                        @endif

                        @if($nome->status === 'pago')
                            <span class="badge bg-danger mt-2">Pago</span>
                        @endif
                    </small>

                {{-- DISPONÍVEL --}}
                @else

                    <form method="POST" action="{{ route('comprar.nome', $nome->id) }}" class="mt-2">
                        @csrf

                        <input type="text" name="comprador_nome" 
                               class="form-control form-control-sm mb-1" 
                               placeholder="Seu nome" required>

                        <input type="text" name="comprador_whatsapp" 
                               class="form-control form-control-sm mb-2" 
                               placeholder="Whatsapp (ex: 11999999999)" required>

                        <button class="btn btn-sm btn-success w-100">Comprar</button>
                    </form>

                @endif

            </div>
        </div>

    @endforeach
</div>

@endsection
