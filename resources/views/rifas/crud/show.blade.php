@extends('layouts.rifa')

@section('content')

<h1 class="mb-3">{{ $rifa->titulo }}</h1>
<a href="{{ route('rifas.gerarNomes.form', $rifa->id) }}"
   class="btn btn-success mb-3">
    ➕ Gerar nomes para esta rifa
</a>

<div class="mb-3">
    <a href="{{ route('rifas.index', absolute: false) }}" class="btn btn-secondary btn-sm">⬅ Voltar</a>

    @if(!$rifa->nomes()->exists())
        <a href="{{ route('rifas.gerarNomes.form', $rifa->id) }}"
           class="btn btn-primary btn-sm">
           ➕ Gerar Nomes
        </a>
    @else
        <button class="btn btn-secondary btn-sm" disabled>
            ✔ Nomes já gerados
        </button>
    @endif

    @if($rifa->sorteado_em)
        <a href="{{ route('admin.rifas.resultado', $rifa->id) }}" class="btn btn-success btn-sm">
            🎉 Ver Resultado
        </a>
    @else
        <a href="{{ route('admin.rifas.sorteio.animado', $rifa->id) }}" class="btn btn-warning btn-sm">
            🎡 Sortear Rifa
        </a>
    @endif
</div>



<div class="card mb-4">
    <div class="card-body">
        <p><strong>Prêmio:</strong> {{ $rifa->premio }}</p>
        <p><strong>Valor por nome:</strong> R$ {{ number_format($rifa->valor,2,',','.') }}</p>
        <p><strong>Status:</strong>
            @if($rifa->status === 'sorteada')
                <span class="badge bg-success">Sorteada</span>
            @else
                <span class="badge bg-secondary">Em aberto</span>
            @endif
        </p>

        <a href="{{ route('rifas.index', absolute: false) }}" class="btn btn-secondary mt-2">Voltar</a>
        <a href="{{ route('rifas.edit', $rifa->id) }}" class="btn btn-primary mt-2">Editar</a>

        <a href="{{ route('rifas.gerarNomes', $rifa->id) }}" class="btn btn-warning mt-2">
            Gerar Nomes
        </a>
    </div>
</div>

<hr>

<h4>Nomes cadastrados</h4>

@if($rifa->nomes->count() === 0)

    <div class="alert alert-info">
        Nenhum nome gerado ainda.
    </div>

@else

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
                <div class="p-3 rounded border" style="background: {{ $cor }}">
                    <strong class="d-block text-center">{{ $nome->nome }}</strong>

                    @if($nome->status !== 'disponivel')
                        <small class="d-block text-center mt-2">
                            {{ $nome->comprador_nome }}<br>
                            <small>{{ $nome->comprador_whatsapp }}</small>
                        </small>
                    @endif
                </div>
            </div>

        @endforeach
    </div>

@endif

@endsection
