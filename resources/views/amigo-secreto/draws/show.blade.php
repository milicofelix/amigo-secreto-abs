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

            @if($draw)

               {{-- Lista responsiva com Flexbox --}}
                <div class="space-y-4 mt-4">
                    @foreach($draw->assignments as $a)
                        @php
                            $gender = $a->giver->gender ?? null;
                            $icon = $gender === 'F' ? '👩' : ($gender === 'M' ? '👨' : '👤');
                            $reveal = $a->giver->reveal;
                        @endphp

                       <div class="participant-row bg-white shadow p-4 rounded-2xl">
    
                            <div class="participant-info">
                                <span class="text-3xl">{{ $icon }}</span>
                                <span class="text-xl font-bold text-gray-800">{{ $a->giver->name }}</span>
                            </div>

                            {{-- AÇÃO (revelou ou não) --}}
                            <div>
                                @if($reveal)
                                    <div class="reveal-box">
                                        <div class="reveal-badge">🔓 Já revelou!</div>
                                        <span class="reveal-date">{{ \Carbon\Carbon::parse($reveal->revealed_at)->format('d/m/Y H:i') }}</span>
                                        {{-- <a href="{{ route('reveal.form', $a->giver) }}" class="reveal-btn">🔁 Rever</a> --}}
                                        <button onclick="window.location='{{ route('participants.qr', ['group' => $group->id, 'id' => $a->giver->id]) }}'"
                                            class="qr-btn"
                                            title="Ver QR Code">
                                            📱
                                        </button>
                                    </div>
                                    <label class="flex items-center gap-2 text-lg font-bold text-indigo-700">
                                        <input type="checkbox" class="w-5 h-5"
                                        onchange="updateStatus({{ $a->giver->id }}, 'shared_gift_idea', this.checked)"
                                        {{ $a->giver->shared_gift_idea ? 'checked disabled' : '' }} />
                                            🎀 Já postei meu presente
                                    </label>

                                    <label class="flex items-center gap-2 text-lg font-bold text-green-700 mt-3">
                                        <input type="checkbox" class="w-5 h-5"
                                            onchange="updateStatus({{ $a->giver->id }}, 'bought_gift', this.checked)"
                                            {{ $a->giver->bought_gift ? 'checked disabled' : '' }} />
                                            🛒 Já comprei o presente
                                    </label>

                                @else
                                    {{-- <a href="{{ route('reveal.form', $a->giver) }}" class="btn-reveal-primary">
                                        🔐 Revelar meu amigo
                                    </a> --}}
                                    <button onclick="window.location='{{ route('participants.qr', ['group' => $group->id, 'id' => $a->giver->id]) }}'"
                                            class="qr-btn"
                                            title="Ver QR Code">
                                            📱 Revelar Amigo Secreto
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Informação do sorteio --}}
                <div class="mt-8 text-center">
                    <div class="inline-block bg-green-100 text-green-700 px-4 py-2 rounded-full text-sm font-semibold">
                        ✅ Sorteio realizado em {{ $draw->drawn_at->format('d/m/Y H:i') }}
                    </div>
                </div>

            @else

                <div class="text-center py-8">
                    <p class="text-gray-600 text-lg">
                        Nenhum sorteio foi realizado ainda 🎲
                    </p>
                </div>

            @endif

            {{-- Voltar --}}
            <div class="mt-8 text-center">
                <a href="{{ route('groups.index', ['year' => $group->eventYear()]) }}"
                class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-semibold transition w-full md:w-auto">
                    ← Voltar aos Grupos
                </a>
            </div>

        </div>

        <div class="text-center text-sm text-gray-500 mt-6">
            💌 Compartilhe este resultado com seu grupo!
        </div>

    </div>

    <!-- Popup de Aviso -->
    <div id="alertMimo" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-2xl shadow-2xl p-8 text-center max-w-sm mx-auto animate-popup border-2 border-red-500">
            
            <div class="text-5xl mb-4 text-yellow-500 animate-pulse">⚠️</div>

            <h2 class="text-xl font-extrabold text-red-600 mb-3">
                Atenção!
            </h2>

            <p class="text-gray-800 font-medium leading-relaxed mb-4">
                O participante que <strong>não comprar o mimo</strong> terá que pagar 
                <strong class="text-red-600">o dobro do mínimo</strong>,
                ou seja:
            </p>

            <p class="text-3xl font-extrabold text-green-600 mb-4 animate-bounce">
                💸 R$ 100,00!
            </p>

            <p class="text-sm text-gray-700 font-semibold mt-2 mb-6 bg-yellow-100 border border-yellow-400 px-3 py-2 rounded-lg inline-block animate-pulse">
                📅 Dia do evento: <strong>31/12/2025</strong> — 🏠 Casa da Gabriela
            </p>

            <button onclick="fecharAviso()"
                    class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg font-semibold transition shadow-md">
                Entendi 👍
            </button>
        </div>
    </div>
    <audio id="popupSound" src="https://assets.mixkit.co/sfx/download/mixkit-positive-notification-951.wav" preload="auto"></audio>

    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>

    <script>
        window.onload = function() {
            const popupKey = 'popupMimoShownAt_{{ $group->id }}';
            const today = new Date().toISOString().slice(0, 10);
            const lastShown = localStorage.getItem(popupKey);

            if (lastShown !== today) {
                document.getElementById('alertMimo').classList.remove('hidden');
                localStorage.setItem(popupKey, today);

                // 🔊 Toca som suave de aviso
                const sound = document.getElementById('popupSound');
                if (sound) {
                    sound.currentTime = 0;
                    sound.play().catch(() => {}); // evita erro em navegação que bloqueia autoplay
                }
            }
        };

       function fecharAviso() {
            const popup = document.getElementById('alertMimo');
            popup.classList.add('hidden');

            // 🔊 Som suave ao confirmar
            const sound = document.getElementById('popupSound');
            if (sound) {
                sound.currentTime = 0;
                sound.play().catch(() => {});
            }

            // 🎉 Explosão de confete
            confetti({
                particleCount: 180,
                spread: 90,
                startVelocity: 50,
                origin: { y: 0.6 }
            });
        }
    </script>


    <script>
        function updateStatus(participantId, field, checked) {

            // ✅ regra de “não desmarcar”
            if (!checked) {
                // força voltar para marcado visualmente
                const selector = `input[data-participant="${participantId}"][data-field="${field}"]`;
                const checkbox = document.querySelector(selector);
                if (checkbox) checkbox.checked = true;
                return;
            }

            fetch("{{ route('participant.status.update') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    participant_id: participantId,
                    field: field,
                    value: 1
                })
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    // 🔒 trava o checkbox depois de marcar
                    const selector = `input[data-participant="${participantId}"][data-field="${field}"]`;
                    const checkbox = document.querySelector(selector);
                    if (checkbox) checkbox.disabled = true;
                }
            })
            .catch(err => {
                console.error(err);
            });
        }
    </script>

@endsection
