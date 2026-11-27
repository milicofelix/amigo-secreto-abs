@extends('layouts.amigo-secreto')

@section('content')
<div class="max-w-6xl mx-auto mt-8 px-4">

    <h1 class="text-2xl md:text-3xl font-bold mb-4 text-black">
        🎁 Gerenciar Grupo: {{ $group->name }}
    </h1>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 text-red-800 p-3 rounded mb-4">{{ session('error') }}</div>
    @endif
    @foreach (['roster', 'participant_id', 'cannot_draw_id', 'collective_reveal_at', 'reason'] as $field)
        @error($field)
            <div class="bg-red-100 text-red-800 p-3 rounded mb-4" role="alert">{{ $message }}</div>
        @enderror
    @endforeach
    @if(session('participant_import_warning'))
        <div role="status" class="bg-yellow-100 text-yellow-900 p-3 rounded mb-4">
            {{ session('participant_import_warning') }}
        </div>
    @endif


    {{-- Participantes --}}
    <div class="bg-white p-4 shadow rounded mb-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold">👥 Participantes</h2>
            @if ($group->user_id === auth()->id() && !$hasCompletedDraw)
                <a href="{{ route('participants.import', $group) }}"
                   class="rounded border border-indigo-600 px-3 py-2 font-semibold text-indigo-700 hover:bg-indigo-50">
                    Importar do ano anterior
                </a>
            @endif
        </div>

        @if ($hasCompletedDraw)
            <p class="mb-4 text-sm text-gray-700">A lista fica bloqueada enquanto o sorteio estiver ativo. Os convites continuam disponíveis.</p>
        @else
        <form method="POST"
              action="{{ route('participants.store', $group) }}"
              enctype="multipart/form-data"
              class="flex flex-col md:flex-row md:flex-wrap gap-2 mb-4">

            @csrf

            <input type="text" name="name" value="{{ old('name') }}" placeholder="Nome" required
                class="border p-2 rounded w-full md:w-[32%]">
            {{-- Foto do participante --}}
            <div class="mb-4">
                <label class="block font-semibold mb-1">Foto (opcional)</label>
                <input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp,.gif,.bmp"
                    class="w-full border rounded p-2">
                <p class="text-sm text-gray-600 mt-1">JPG, PNG, WebP, GIF ou BMP, até 2 MB.</p>

                @error('photo')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <input type="text" name="whatsapp" value="{{ old('whatsapp') }}" placeholder="WhatsApp"
                class="border p-2 rounded w-full md:w-[32%]">

            <input type="email" name="email" value="{{ old('email') }}" placeholder="E-mail"
                class="border p-2 rounded w-full md:w-[32%]">

            <input type="text" name="wishlist" value="{{ old('wishlist') }}" placeholder="Desejos (opcional)"
                class="border p-2 rounded w-full md:w-[32%]">

            <button class="bg-green-600 text-white px-4 py-2 rounded w-full md:w-auto">
                + Adicionar
            </button>
            @foreach (['name', 'email', 'whatsapp', 'wishlist'] as $field)
                @error($field)
                    <p class="w-full text-sm text-red-600">{{ $message }}</p>
                @enderror
            @endforeach
        </form>
        @endif

        {{-- Tabela responsiva --}}
       <div class="overflow-x-auto rounded">
            <table class="w-full border-collapse text-left shadow-sm rounded-lg overflow-hidden compact-table">
                <thead>
                    <tr class="bg-gray-100 text-left">
                        <th class="p-2 border">Nome</th>
                        <th class="p-2 border">E-mail</th>
                        <th class="p-2 border">WhatsApp</th>
                        <th class="p-2 border w-24 text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($group->participants as $p)
                    <tr>
                        <td class="p-2 border">{{ $p->name }}</td>
                        <td class="p-2 border">{{ $p->masked_email }}</td>
                        <td class="p-2 border">
                            {{ $p->whatsapp ? $p->masked_whatsapp : (filled($p->getRawOriginal('whatsapp')) ? 'Número indisponível' : '—') }}
                        </td>
                        <td class="p-2 border text-center">
                            <div class="flex flex-wrap justify-center gap-2">
                                <a href="{{ route('participants.qr', [$group, $p], absolute: false) }}"
                                   class="rounded bg-indigo-600 px-2 py-1 text-white">Convite</a>
                                @unless ($hasCompletedDraw)
                                <a href="{{ route('participants.edit', [$group, $p]) }}"
                                class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded">✏️</a>

                                <form action="{{ route('participants.destroy', [$group, $p]) }}"
                                    method="POST"
                                    onsubmit="return confirm('Remover participante?')">
                                    @csrf @method('DELETE')
                                    <button class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded">
                                        🗑️
                                    </button>
                                </form>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

    </div>

    {{-- Restrições --}}
    <div class="bg-white p-4 shadow rounded mb-6">
        <h2 class="text-xl font-semibold mb-3">🚫 Restrições</h2>

        @unless ($hasCompletedDraw)
        <form method="POST"
              action="{{ route('restrictions.store', $group) }}"
              class="flex flex-col md:flex-row gap-2 mb-4">

            @csrf

            <select name="participant_id" required
                class="border p-2 rounded w-full md:w-1/3">
                <option value="">Quem</option>
                @foreach($group->participants as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </select>

            <select name="cannot_draw_id" required
                class="border p-2 rounded w-full md:w-1/3">
                <option value="">Não pode tirar</option>
                @foreach($group->participants as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </select>

            <button class="bg-yellow-600 text-white px-4 py-2 rounded w-full md:w-auto">
                + Adicionar
            </button>
        </form>
        @endunless

        <ul class="list-disc pl-4 md:pl-6">
            @foreach($group->restrictions as $r)
                <li class="flex flex-col md:flex-row justify-between border-b py-1 gap-2">
                    <span>{{ $r->participant->name }} ❌ {{ $r->cannotDraw->name }}</span>

                    @unless ($hasCompletedDraw)
                    <form action="{{ route('restrictions.destroy', [$group, $r]) }}"
                          method="POST">
                        @csrf @method('DELETE')
                        <button class="text-red-600 hover:underline">
                            Remover
                        </button>
                    </form>
                    @endunless
                </li>
            @endforeach
        </ul>
    </div>


    <section class="bg-white p-4 shadow rounded mb-6">
        <h2 class="text-xl font-semibold mb-2">Revelação coletiva</h2>
        @php($revealAt = $group->collectiveRevealAt())
        @if ($revealAt)
            <p class="mb-3">Agendada para {{ $revealAt->format('d/m/Y H:i') }} (horário de Brasília).</p>
        @else
            <p class="mb-3">Defina quando o organizador poderá ver todos os pares.</p>
        @endif
        @if (!$hasCompletedDraw || !$revealAt)
            <form method="POST" action="{{ route('draw.schedule', $group) }}" class="flex flex-wrap items-end gap-3">
                @csrf
                @method('PATCH')
                <label class="block" for="collective-reveal-at">
                    Data e hora (Brasília)
                    <input id="collective-reveal-at" type="datetime-local" name="collective_reveal_at" required
                           value="{{ old('collective_reveal_at', $revealAt?->format('Y-m-d\TH:i')) }}"
                           class="block border rounded p-2">
                </label>
                <button class="rounded bg-indigo-600 px-4 py-2 text-white">Agendar revelação</button>
            </form>
        @endif
    </section>

    {{-- Sorteio --}}
    <div class="bg-white p-4 shadow rounded text-center">
        <h2 class="text-xl font-semibold mb-3">{{ $hasCompletedDraw ? '🎲 Sorteio realizado' : '🎲 Realizar Sorteio' }}</h2>

        @if ($hasCompletedDraw)
            <a href="{{ route('draw.show', $group) }}"
               class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-semibold transition">
                👁️ Ver Resultado do Sorteio
            </a>
        @else
            <form action="{{ route('draw.store', $group) }}"
                  method="POST"
                  onsubmit="return confirm('Deseja realmente sortear?')">
                @csrf
                <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded text-lg w-full md:w-auto">
                    Sortear Amigo Secreto
                </button>
            </form>
        @endif
        @if ($hasCompletedDraw && !$group->canRevealCollectively())
            <details class="mt-5 border-t pt-4 text-left">
                <summary class="cursor-pointer font-semibold text-red-700">Cancelar sorteio</summary>
                <p class="my-2 text-sm text-gray-700">O resultado individual deixará de aparecer e os registros de presente deste sorteio serão reiniciados.</p>
                <form method="POST" action="{{ route('draw.cancel', $group) }}"
                      onsubmit="return confirm('Cancelar o sorteio atual?')">
                    @csrf
                    <label class="block" for="draw-cancel-reason">Motivo do cancelamento</label>
                    <textarea id="draw-cancel-reason" name="reason" maxlength="500" required rows="2"
                              class="block w-full border rounded p-2 mb-3">{{ old('reason') }}</textarea>
                    <button class="rounded bg-red-700 px-4 py-2 text-white">Confirmar cancelamento</button>
                </form>
            </details>
        @endif
    </div>

    @include('amigo-secreto.draws.history')
</div>
@endsection
