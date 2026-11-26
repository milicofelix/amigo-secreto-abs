@extends('layouts.adm-rifa')

@section('content')

<div class="text-center mt-4">

    <h1>🎉 Resultado do Sorteio</h1>
    <h3 class="mt-4">{{ $rifa->titulo }}</h3>

    <div class="mt-5 p-4 bg-light border rounded" style="max-width: 480px; margin:auto;">
        
        <h4>Nome sorteado:</h4>
        @if($sorteado)
            <h2 class="display-4">{{ $sorteado->nome }}</h2>
        @else
            <h2 class="display-4 text-danger">Erro ao identificar nome sorteado</h2>
        @endif

        <hr>

        <h5>Ganhador:</h5>

       @if($existeVencedor)
            <p>
                <strong>{{ $sorteado->comprador_nome }}</strong><br>
                <small>{{ $sorteado->comprador_whatsapp }}</small>
            </p>
        @else
            <p class="text-danger fw-bold">Nenhum vencedor</p>
            <p><small>(Nome não estava pago)</small></p>
        @endif

        <hr>

        <a href="{{ route('rifas.show', $rifa->id) }}" class="btn btn-primary mt-3">
            Ver cartela pública
        </a>

    </div>
</div>

@endsection
