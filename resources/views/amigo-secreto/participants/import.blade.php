@extends('layouts.amigo-secreto')

@section('title', 'Importar participantes')

@section('content')
<section class="mx-auto max-w-2xl rounded-lg bg-white p-6 shadow">
    <h1 class="mb-2 text-2xl font-bold text-indigo-700">Importar participantes de {{ $year }}</h1>
    <p class="mb-5 text-gray-700">Grupo de destino: <strong>{{ $group->name }}</strong></p>

    @if ($errors->any())
        <ul role="alert" class="mb-4 list-disc rounded bg-red-50 py-3 pl-8 pr-3 text-red-800">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    @if ($hasCompletedDraw)
        <p>Este grupo já realizou o sorteio. A importação está encerrada.</p>
    @elseif ($sources->isEmpty())
        <p>Nenhum grupo de {{ $year }} está disponível para importar.</p>
    @else
        <form method="GET" action="{{ route('participants.import', $group) }}" class="mb-5 flex flex-wrap items-end gap-3">
            <div class="flex-1">
                <label for="import-source" class="mb-1 block font-semibold">Grupo de origem</label>
                <select id="import-source" name="source" class="w-full rounded border-gray-300" required>
                    <option value="">Escolha um grupo</option>
                    @foreach ($sources as $candidate)
                        <option value="{{ $candidate->id }}" @selected($source?->id === $candidate->id)>
                            {{ $candidate->name }} ({{ $candidate->participants_count }})
                        </option>
                    @endforeach
                </select>
            </div>
            <button class="rounded bg-gray-100 px-4 py-2 font-semibold hover:bg-gray-200">Ver participantes</button>
        </form>

        @if ($source)
            @if ($participants->isEmpty())
                <p>Esse grupo não tem participantes cadastrados.</p>
            @else
                <form method="POST" action="{{ route('participants.import.store', $group) }}">
                    @csrf
                    <input type="hidden" name="source" value="{{ $source->id }}">
                    <fieldset>
                        <legend class="font-semibold">Quem vai participar de novo?</legend>
                        <p class="mb-3 text-sm text-gray-600">Desmarque quem não vai participar. Os dados do sorteio anterior ficam no grupo de origem.</p>
                        <div class="mb-4 divide-y rounded border">
                            @foreach ($participants as $participant)
                                @php($imported = in_array($participant->id, $alreadyImported, true))
                                <label class="flex items-center gap-3 px-4 py-3">
                                    <input type="checkbox" name="participants[]" value="{{ $participant->id }}"
                                           @disabled($imported)
                                           @checked(!$imported && in_array($participant->id, (array) old('participants', $participants->modelKeys())))>
                                    <span>{{ $participant->name }}</span>
                                    @if ($imported)
                                        <span class="ml-auto text-sm text-gray-500">Já importado</span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                    <button class="rounded bg-indigo-600 px-4 py-2 font-semibold text-white disabled:opacity-50"
                            @disabled($participants->every(fn ($participant) => in_array($participant->id, $alreadyImported, true)))>
                        Importar selecionados
                    </button>
                </form>
            @endif
        @endif
    @endif

    <a href="{{ route('groups.edit', $group, absolute: false) }}" class="mt-5 inline-block font-semibold text-indigo-700 underline">Voltar ao grupo</a>
</section>
@endsection
