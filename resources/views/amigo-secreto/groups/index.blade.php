@extends('layouts.amigo-secreto')

@section('content')
<div class="max-w-5xl mx-auto mt-6 px-4">

    <h1 class="text-2xl md:text-3xl font-bold mb-6 text-black">
        🎁 Meus Grupos de Amigo Secreto
    </h1>

    <a href="{{ route('groups.create', ['year' => $selectedYear]) }}"
       class="bg-green-600 text-white px-4 py-2 rounded mb-6 inline-block hover:bg-green-700">
        + Novo Grupo
    </a>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif


    {{-- LISTAGEM DOS GRUPOS --}}
    @forelse ($groups as $group)
        <div class="bg-white shadow rounded p-4 mb-4">
            <h2 class="font-semibold text-xl">{{ $group->name }}</h2>
            <p class="text-gray-600">{{ $group->description }}</p>

            <p class="text-sm text-gray-500 mt-2">
                <strong>Data do evento:</strong> {{ $group->event_date ?? 'não definida' }} <br>
                <strong>Local:</strong> {{ $group->event_location ?? 'não informado' }}
            </p>

            <div class="mt-4 flex flex-col sm:flex-row sm:items-center gap-2">

                {{-- GERENCIAR --}}
                <a href="{{ route('groups.edit', $group) }}"
                   class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 w-full sm:w-auto text-center">
                    Gerenciar
                </a>

                {{-- VER RESULTADO --}}
                @if($group->draws()->where('status', 'completed')->exists())
                    <a href="{{ route('draw.show', ['group' => $group->id]) }}"
                       class="bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700 w-full sm:w-auto text-center text-sm">
                        👁️ Ver Resultado
                    </a>
                @endif

                {{-- O servidor verifica se esta conta criou o grupo. --}}
                <form method="POST"
                      action="{{ route('groups.destroy', $group) }}"
                      class="w-full sm:w-auto"
                      onsubmit="return confirm('Excluir este grupo e seus participantes?')">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 w-full">
                        Excluir
                    </button>
                </form>

            </div>
        </div>
    @empty
        <p class="rounded bg-white p-4 text-gray-700">Nenhum grupo em {{ $selectedYear }}.</p>
    @endforelse


</div>
@endsection
