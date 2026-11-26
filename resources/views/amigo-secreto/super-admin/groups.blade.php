@extends('layouts.amigo-secreto')

@section('title', 'Super Admin · Grupos')

@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    <section class="rounded-lg bg-white p-5 shadow">
        <h1 class="text-2xl font-bold text-indigo-700">Todos os grupos</h1>
        <p class="mt-1 text-sm text-gray-700">Painel de consulta do Super Admin.</p>

        @if ($errors->any())
            <p role="alert" class="mt-3 text-sm text-red-700">Confira os filtros e tente novamente.</p>
        @endif

        <form method="GET" action="{{ route('super-admin.groups', absolute: false) }}"
              class="my-5 flex flex-wrap items-end gap-3">
            <div class="min-w-28">
                <label for="global-year" class="mb-1 block text-sm font-semibold">Ano</label>
                <select id="global-year" name="year" class="w-full rounded border border-gray-300 bg-white px-3 py-2">
                    <option value="">Todos</option>
                    @foreach ($years as $year)
                        <option value="{{ $year }}" @selected((string) ($filters['year'] ?? '') === (string) $year)>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-44">
                <label for="global-creator" class="mb-1 block text-sm font-semibold">Criador</label>
                <select id="global-creator" name="creator" class="w-full rounded border border-gray-300 bg-white px-3 py-2">
                    <option value="">Todos</option>
                    <option value="unassigned" @selected(($filters['creator'] ?? '') === 'unassigned')>Sem vínculo</option>
                    @foreach ($creators as $creator)
                        <option value="{{ $creator->id }}" @selected(($filters['creator'] ?? '') === (string) $creator->id)>
                            {{ $creator->name }} (#{{ $creator->id }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-36">
                <label for="global-draw" class="mb-1 block text-sm font-semibold">Situação do sorteio</label>
                <select id="global-draw" name="draw" class="w-full rounded border border-gray-300 bg-white px-3 py-2">
                    <option value="">Todas</option>
                    <option value="pending" @selected(($filters['draw'] ?? '') === 'pending')>Pendente</option>
                    <option value="completed" @selected(($filters['draw'] ?? '') === 'completed')>Realizado</option>
                </select>
            </div>
            <button class="rounded bg-indigo-600 px-4 py-2 font-semibold text-white">Filtrar</button>
            @if (array_filter($filters))
                <a href="{{ route('super-admin.groups', absolute: false) }}"
                   class="px-2 py-2 text-sm font-semibold text-indigo-700 underline">Limpar filtros</a>
            @endif
        </form>

        <p class="mb-3 text-sm text-gray-600">{{ $groups->total() }} {{ $groups->total() === 1 ? 'grupo encontrado' : 'grupos encontrados' }}</p>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th scope="col" class="p-3">Grupo</th>
                        <th scope="col" class="p-3">Ano</th>
                        <th scope="col" class="p-3">Criador</th>
                        <th scope="col" class="p-3">Participantes</th>
                        <th scope="col" class="p-3">Sorteio</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($groups as $listedGroup)
                        <tr>
                            <td class="p-3 font-semibold">{{ $listedGroup->name }} <span class="font-normal text-gray-500">#{{ $listedGroup->id }}</span></td>
                            <td class="p-3">{{ $listedGroup->eventYear() }}</td>
                            <td class="p-3">
                                @if ($listedGroup->user)
                                    {{ $listedGroup->user->name }}
                                @else
                                    <span class="rounded bg-amber-100 px-2 py-1 text-amber-900">Sem vínculo</span>
                                @endif
                            </td>
                            <td class="p-3">{{ $listedGroup->participants_count }}</td>
                            <td class="p-3">{{ $listedGroup->has_completed_draw ? 'Realizado' : 'Pendente' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-3 text-gray-600">Nenhum grupo encontrado para os filtros escolhidos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $groups->links() }}</div>
    </section>

    <section class="rounded-lg bg-white p-5 shadow">
        <h2 class="mb-3 text-xl font-semibold text-indigo-700">Histórico de confirmação de criadores</h2>
        <ul class="divide-y">
            @forelse ($confirmations as $confirmation)
                <li class="py-3 text-sm">
                    <strong>{{ $confirmation->group_name }}</strong> · {{ $confirmation->creator_name }}
                    <span class="block text-gray-600">{{ \Illuminate\Support\Carbon::parse($confirmation->confirmed_at)->format('d/m/Y H:i') }}</span>
                </li>
            @empty
                <li class="text-sm text-gray-600">Nenhuma confirmação registrada desde a ativação do histórico.</li>
            @endforelse
        </ul>
        <div class="mt-4">{{ $confirmations->links() }}</div>
    </section>
</div>
@endsection
