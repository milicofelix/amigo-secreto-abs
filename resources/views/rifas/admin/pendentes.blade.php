@extends('layouts.adm-rifa')

@section('content')

{{-- @if(!$rifa->sorteado_em)
        <a href="{{ route('admin.rifas.sortear', $rifa->id) }}" 
           class="btn btn-danger mb-3">
            🎲 Sortear Ganhador
        </a>
    @else
        <div class="alert alert-success">
            🎉 <strong>Ganhador já sorteado!</strong><br>
            Número: {{ $rifa->ganhador_numero }}<br>
            Nome: {{ $rifa->ganhador_nome }}<br>
            WhatsApp: {{ $rifa->ganhador_whatsapp }}
        </div>
    @endif --}}

<h1>Pagamentos Pendentes</h1>

<table class="table">
    <tr>
        <th>Nome</th>
        <th>Comprador</th>
        <th>WhatsApp</th>
        <th>Ações</th>
    </tr>

    @foreach($pendentes as $n)
        <tr>
            <td>{{ $n->nome }}</td>
            <td>{{ $n->comprador_nome }}</td>
            <td>{{ $n->comprador_whatsapp }}</td>
            <td>
                <form method="POST" action="{{ url('/admin/rifas/pagar/'.$n->id) }}">
                    @csrf
                    <button class="btn btn-success btn-sm">Confirmar Pagamento</button>
                </form>
            </td>
        </tr>
    @endforeach
</table>

@endsection
