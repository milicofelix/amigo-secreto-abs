@extends('layouts.rifa')

@section('content')
<h1>Rifas Disponíveis</h1>

@if($rifas->count() === 0)
    <p>Nenhuma rifa cadastrada.</p>
@else
    <ul class="list-group">
        @foreach($rifas as $rifa)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <strong>{{ $rifa->titulo }}</strong><br>
                    <small>Prêmio: {{ $rifa->premio }}</small>
                </div>

                <a href="{{ route('rifas.show', $rifa->id) }}" class="btn btn-primary">
                    Ver Cartela
                </a>
            </li>
        @endforeach
    </ul>
@endif

@endsection
