@extends('layouts.adm-rifa')

@section('content')

<div class="text-center mt-4">

    <h1>🎉 Resultado do Sorteio</h1>
    <h3 class="mt-4">{{ $rifa->titulo }}</h3>

    <div class="mt-5 p-4 bg-light border rounded winner-card" style="max-width: 480px; margin:auto;">
        
        <h4>Nome sorteado:</h4>
        <h2 class="display-4 fw-bold">
            {{ $sorteado->nome }}
        </h2>

        <hr>

        <h5>Ganhador:</h5>

        @if($existeVencedor)
            <p class="fs-4 text-success fw-bold">
                {{ $sorteado->comprador_nome }}<br>
                <small class="d-block mt-1">
                    {{ $sorteado->comprador_whatsapp }}
                </small>
            </p>
        @else
            <p class="text-danger fw-bold mb-1">Nenhum vencedor</p>
            <p>
                <small>
                    (Nome sorteado não estava pago – status: {{ strtoupper($sorteado->status) }})
                </small>
            </p>
        @endif

        <hr>

        <a href="{{ route('rifas.show', $rifa->id) }}" class="btn btn-primary mt-3">
            Ver cartela pública
        </a>

    </div>
</div>

@endsection

@push('styles')
<style>
    /* animação simples para dar impacto no resultado */
    .winner-card{
        animation: winner-pop 0.8s ease-out;
    }

    @keyframes winner-pop{
        0%{
            transform: scale(0.6);
            opacity: 0;
        }
        60%{
            transform: scale(1.05);
            opacity: 1;
        }
        100%{
            transform: scale(1);
        }
    }
</style>
@endpush
