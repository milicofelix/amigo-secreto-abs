@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-3">📱 Logs de Envio WhatsApp</h2>

    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Telefone</th>
                <th>Mensagem</th>
                <th>Status</th>
                <th>Data</th>
                <th>Resposta / Erro</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
                <tr>
                    <td>{{ $log->id }}</td>
                    <td>{{ $log->phone }}</td>
                    <td>{{ Str::limit($log->message, 80) }}</td>
                    <td>
                        @if($log->status === 'success')
                            <span class="badge bg-success">Sucesso</span>
                        @elseif($log->status === 'failed')
                            <span class="badge bg-danger">Falha</span>
                        @else
                            <span class="badge bg-warning text-dark">Pendente</span>
                        @endif
                    </td>
                    <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                    <td>
                        @if($log->error_message)
                            <code>{{ Str::limit($log->error_message, 100) }}</code>
                        @elseif($log->response)
                            <code>{{ Str::limit(json_encode($log->response), 100) }}</code>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-3">
        {{ $logs->links() }}
    </div>
</div>
@endsection
