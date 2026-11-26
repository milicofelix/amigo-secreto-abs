@extends('layouts.rifa')

@section('content')

<h1 class="mb-3">Rifas Cadastradas</h1>

<a href="{{ route('crud.rifas.create') }}" class="btn btn-success mb-3">
    ➕ Criar Nova Rifa
</a>

<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Título</th>
            <th>Prêmio</th>
            <th>Valor</th>
            <th>Nomes</th>
            <th>Ações</th>
        </tr>
    </thead>

    <tbody>
        @foreach($rifas as $rifa)
            <tr>
                <td>{{ $rifa->id }}</td>
                <td>{{ $rifa->titulo }}</td>
                <td>{{ $rifa->premio }}</td>
                <td>R$ {{ number_format($rifa->valor,2,',','.') }}</td>
                <td>{{ $rifa->nomes->count() }}</td>
                <td>
                    <a href="{{ route('crud.rifas.show', $rifa->id) }}" class="btn btn-sm btn-primary">
                        Ver
                    </a>

                    <a href="{{ route('crud.rifas.edit', $rifa->id) }}" class="btn btn-sm btn-warning">
                        Editar
                    </a>

                    <form action="{{ route('crud.rifas.destroy', $rifa->id) }}" 
                          method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">
                            Excluir
                        </button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@endsection
