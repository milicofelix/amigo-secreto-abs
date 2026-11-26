@extends('layouts.rifa')

@section('content')

<h1>Editar Rifa</h1>

<form method="POST" action="{{ route('crud.rifas.update', $rifa->id) }}">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label>Título</label>
        <input type="text" name="titulo" value="{{ $rifa->titulo }}" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Prêmio</label>
        <input type="text" name="premio" value="{{ $rifa->premio }}" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Valor por Nome</label>
        <input type="number" step="0.01" name="valor" value="{{ $rifa->valor }}" class="form-control" required>
    </div>

    <button class="btn btn-primary">Salvar Alterações</button>
    <a href="{{ route('rifas.index') }}" class="btn btn-secondary">Cancelar</a>

</form>

@endsection
