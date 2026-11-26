@extends('layouts.rifa')

@section('content')

<h2>Gerar nomes para: <strong>{{ $rifa->titulo }}</strong></h2>

<form method="POST" action="{{ route('rifas.gerarNomes', $rifa->id) }}" class="mt-4">
    @csrf

    <label class="form-label">Quantidade de nomes:</label>
    <input type="number" name="quantidade" class="form-control"
           placeholder="Ex: 50" required min="1">

    <button class="btn btn-success mt-3">Gerar</button>
</form>

@endsection
