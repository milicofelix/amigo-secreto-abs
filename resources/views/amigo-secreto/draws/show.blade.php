@extends('layouts.amigo-secreto')

<style>
    .reveal-again-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 3px 6px rgba(0,0,0,0.08);
    }
    .reveal-again-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 10px rgba(0,0,0,0.12);
    }
    .qr-btn {
        border: none;
        background: transparent;
        font-size: 1.2rem;
        cursor: pointer;
        padding-left: 6px;
        transition: transform 0.2s ease, opacity 0.2s;
        opacity: 0.5;
    }

    .qr-btn:hover {
        transform: scale(1.15);
        opacity: 1;
    }

    .participant-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    @media (max-width: 480px) {
        .qr-btn {
            font-size: 1.4rem;
            opacity: 0.8;
        }
    }


</style>


@section('content')
    <div class="max-w-3xl mx-auto mt-10 px-4">

        <div class="bg-white shadow-lg rounded-lg p-6 md:p-8 border border-gray-200">

            <h1 class="text-2xl md:text-3xl font-bold text-indigo-700 text-center mb-6">
                🎁 Resultado do Sorteio<br>
                <span class="text-lg md:text-xl text-gray-600">— {{ $group->name }} —</span>
            </h1>

            @if(session('warning'))
                <p class="mb-4 rounded bg-yellow-100 p-3 text-yellow-900" role="status">{{ session('warning') }}</p>
            @endif

            @if($draw)
                @if ($revealAt = $group->collectiveRevealAt())
                    <p class="text-center text-gray-700">Revelação coletiva em {{ $revealAt->format('d/m/Y H:i') }} (horário de Brasília).</p>
                @else
                    <p class="text-center text-gray-700">A revelação coletiva ainda precisa ser agendada no grupo.</p>
                @endif

               {{-- Lista responsiva com Flexbox --}}
                <div class="space-y-4 mt-4">
                    @foreach($draw->assignments as $a)
                        @php
                            $gender = $a->giver->gender ?? null;
                            $icon = $gender === 'F' ? '👩' : ($gender === 'M' ? '👨' : '👤');
                            $revealedAt = $a->giver->first_revealed_at;
                        @endphp

                       <div class="participant-row bg-white shadow p-4 rounded-2xl">
    
                            <div class="participant-info">
                                <span class="text-3xl">{{ $icon }}</span>
                                <span class="text-xl font-bold text-gray-800">{{ $a->giver->name }}</span>
                            </div>

                            {{-- AÇÃO (revelou ou não) --}}
                            <div>
                                @if($revealedAt)
                                    <div class="reveal-box">
                                        <div class="reveal-badge">🔓 Já revelou!</div>
                                        <span class="reveal-date">{{ $revealedAt->format('d/m/Y H:i') }}</span>
                                        {{-- <a href="{{ route('reveal.form', $a->giver) }}" class="reveal-btn">🔁 Rever</a> --}}
                                        <button onclick="window.location='{{ route('participants.qr', ['group' => $group->id, 'participant' => $a->giver->id]) }}'"
                                            class="qr-btn"
                                            title="Gerenciar convite">
                                            📱
                                        </button>
                                    </div>
                                    <label class="flex items-center gap-2 text-lg font-bold text-indigo-700">
                                        <input type="checkbox" class="w-5 h-5" disabled @checked($a->giver->shared_gift_idea) />
                                            🎀 Ideia de presente compartilhada
                                    </label>

                                    <label class="flex items-center gap-2 text-lg font-bold text-green-700 mt-3">
                                        <input type="checkbox" class="w-5 h-5" disabled @checked($a->giver->bought_gift) />
                                            🛒 Presente comprado
                                    </label>

                                @else
                                    {{-- <a href="{{ route('reveal.form', $a->giver) }}" class="btn-reveal-primary">
                                        🔐 Revelar meu amigo
                                    </a> --}}
                                    <button onclick="window.location='{{ route('participants.qr', ['group' => $group->id, 'participant' => $a->giver->id]) }}'"
                                            class="qr-btn"
                                            title="Gerenciar convite">
                                            📱 Gerenciar convite
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Informação do sorteio --}}
                <div class="mt-8 text-center">
                    <div class="inline-block bg-green-100 text-green-700 px-4 py-2 rounded-full text-sm font-semibold">
                        ✅ Sorteio realizado em {{ ($draw->drawn_at ?? $draw->created_at)?->format('d/m/Y H:i') }}
                    </div>
                </div>

            @else

                <div class="text-center py-8">
                    <p class="text-gray-600 text-lg">
                        Nenhum sorteio foi realizado neste grupo.
                    </p>
                    <a href="{{ route('groups.edit', $group) }}"
                       class="mt-4 inline-block rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700">
                        Gerenciar grupo e realizar sorteio
                    </a>
                </div>

            @endif

            {{-- Voltar --}}
            <div class="mt-8 text-center">
                @if ($draw)
                    <a href="{{ route('pwa.participant') }}" class="mr-3 font-semibold text-indigo-700 underline">Atualizar meu presente</a>
                @endif
                <a href="{{ route('groups.index', ['year' => $group->eventYear()], absolute: false) }}"
                class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-semibold transition w-full md:w-auto">
                    ← Voltar aos Grupos
                </a>
            </div>

        </div>


    </div>
@endsection
