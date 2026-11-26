@extends('layouts.rifa')

@section('content')

<h1>Criar Nova Rifa</h1>

<form method="POST" action="{{ route('rifas.store') }}">
    @csrf

    <div class="mb-3">
        <label>Título</label>
        <input type="text" name="titulo" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Prêmio</label>
        <input type="text" name="premio" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Valor por Nome</label>
        <input type="number" step="0.01" name="valor" class="form-control" required>
    </div>

    <button class="btn btn-success">Salvar</button>
    <a href="{{ route('rifas.index') }}" class="btn btn-secondary">Cancelar</a>

</form>

@endsection
