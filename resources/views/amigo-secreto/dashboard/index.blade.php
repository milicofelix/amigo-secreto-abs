@extends('layouts.amigo-secreto')
<style>
    /* Animação suave dos números dos cards */
    .animate-stat {
        opacity: 0;
        transform: translateY(10px);
        animation: statFadeIn 0.6s ease-out forwards;
    }

    @keyframes statFadeIn {
        0% { opacity: 0; transform: translateY(10px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    @media (min-width: 1280px) {
        .card-3d p {
            font-size: 1.8rem !important;
        }
    }
</style>



@section('content')

    <div class="max-w-5xl text-center mx-auto mt-10 px-4">

        <h1 class="text-3xl font-bold text-indigo-700 text-center mb-8">
            📊 Dashboard — <span class="text-gray-600">{{ $group->name }}</span>
        </h1>

        {{-- CARDS --}}
       <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-6 gap-5">

        {{-- Total Participantes --}}
        <div class="bg-blue-100 p-4 rounded-xl shadow-md border-l-4 border-blue-500 card-3d">
            <h2 class="font-bold text-blue-800 text-lg">👥 Participantes</h2>
            <p class="text-2xl font-extrabold text-blue-700 mt-1 animate-stat">{{ $participants }}</p>
        </div>

        {{-- Já Revelaram (com label inteligente) --}}
        <div class="bg-green-100 p-4 rounded-xl shadow-md border-l-4 border-green-500 card-3d">
            <h2 class="font-bold text-green-800 text-lg">
                {{ $percent == 100 ? '🎉 Todos revelaram!' : '🔓 Já revelaram' }}
            </h2>
            <p class="text-2xl font-extrabold text-green-700 mt-1 animate-stat">{{ $revealed }}</p>
        </div>

        {{-- Não Revelaram – só aparece se > 0 --}}
        @if($notRevealed > 0)
        <div class="bg-red-100 p-4 rounded-xl shadow-md border-l-4 border-red-500 card-3d">
            <h2 class="font-bold text-red-800 text-lg">🔐 Não revelaram</h2>
            <p class="text-2xl font-extrabold text-red-700 mt-1 animate-stat">{{ $notRevealed }}</p>
        </div>
        @endif

        {{-- % Revelação --}}
        <div class="bg-yellow-100 p-4 rounded-xl shadow-md border-l-4 border-yellow-500 card-3d">
            <h2 class="font-bold text-yellow-800 text-lg">📈 % Revelação</h2>
            <p class="text-2xl font-extrabold text-yellow-700 mt-1 animate-stat">{{ $percent }}%</p>
        </div>

        {{-- Wishlist feita --}}
        <div class="bg-pink-100 p-4 rounded-xl shadow-md border-l-4 border-pink-500 card-3d">
            <h2 class="font-bold text-pink-800 text-lg">
                {{ $giftIdeaPosted == $participants && $participants > 0 
                    ? '🎁 Todos fizeram a lista' 
                    : '🎁 Lista de desejos feita' }}
            </h2>
            <p class="text-2xl font-extrabold text-pink-700 mt-1 animate-stat">
                {{ $giftIdeaPosted }}
            </p>
        </div>

        {{-- Presente comprado --}}
        <div class="bg-purple-100 p-4 rounded-xl shadow-md border-l-4 border-purple-500 card-3d">
            <h2 class="font-bold text-purple-800 text-lg">
                {{ $giftBought == $participants && $participants > 0 
                    ? '🛍️ Todos já compraram' 
                    : '🛒 Comprado' }}
            </h2>
            <p class="text-2xl font-extrabold text-purple-700 mt-1 animate-stat">
                {{ $giftBought }}
            </p>
        </div>
    </div>

        {{-- LISTA DE ÚLTIMOS QUE REVELARAM --}}
        <div class="mt-10 bg-white shadow-lg rounded-lg p-6 border border-gray-200">
            <h2 class="text-2xl font-bold text-indigo-700 mb-4">⏱️ Últimos que revelaram</h2>

            @if($latestReveals->isEmpty())
                <p class="text-gray-600">Nenhuma revelação ainda...</p>
            @else
                @foreach($latestReveals as $item)
                    <div class="flex justify-between bg-indigo-50 p-3 rounded-lg mb-2">
                        <span class="font-semibold text-indigo-800">
                            {{ $item->name }}
                        </span>
                        <span class="text-gray-700 text-sm">
                            📅 {{ $item->first_revealed_at->format('d/m H:i') }}
                        </span>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- 🎀 Últimos que postaram wishlist --}}
        <div class="mt-10 bg-white shadow-lg rounded-lg p-6 border border-gray-200">
            <h2 class="text-2xl font-bold text-pink-600 mb-4">🎀 Últimos que postaram lista de desejos</h2>

            @if($latestGiftIdeas->isEmpty())
                <p class="text-gray-600">Ninguém atualizou ainda...</p>
            @else
                @foreach($latestGiftIdeas as $item)
                    <div class="flex justify-between bg-pink-50 p-3 rounded-lg mb-2">
                        <span class="font-semibold text-pink-800">{{ $item->name }}</span>
                        <span class="text-gray-700 text-sm">
                            📅 {{ \Carbon\Carbon::parse($item->shared_gift_idea_at)->format('d/m H:i') }}
                        </span>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- 🛒 Últimos que compraram o presente --}}
        <div class="mt-6 bg-white shadow-lg rounded-lg p-6 border border-gray-200">
            <h2 class="text-2xl font-bold text-green-700 mb-4">🛒 Últimos que compraram</h2>

            @if($latestBoughtGifts->isEmpty())
                <p class="text-gray-600">Ainda ninguém informou a compra...</p>
            @else
                @foreach($latestBoughtGifts as $item)
                    <div class="flex justify-between bg-green-50 p-3 rounded-lg mb-2">
                        <span class="font-semibold text-green-800">{{ $item->name }}</span>
                        <span class="text-gray-700 text-sm">
                            📅 {{ \Carbon\Carbon::parse($item->bought_gift_at)->format('d/m H:i') }}
                        </span>
                    </div>
                @endforeach
            @endif
        </div>

        <section class="mt-6 rounded bg-white p-5 shadow">
            <h2 class="mb-2 text-xl font-semibold">Participantes do grupo</h2>
            <ul class="grid gap-2 sm:grid-cols-2">
                @forelse ($members as $listedMember)
                    <li class="rounded bg-gray-50 px-3 py-2">{{ $listedMember->name }}</li>
                @empty
                    <li>Este grupo ainda não tem participantes.</li>
                @endforelse
            </ul>
        </section>

        {{-- BOTOES --}}
        <div class="mt-8 flex flex-wrap justify-center gap-4">
            <a href="{{ route('draw.show', $group->id) }}"
            class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-semibold">
            🎁 Ver Sorteio
            </a>

            <a href="{{ route('reveal.list', $group->id) }}"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">
            👀 Ver Revelações
            </a>

            @can('manage', $group)
            <a href="{{ route('dashboard.start-draw', $group->id) }}"
                class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-semibold">
                🎉 Sortear quem começa
            </a>
            @endcan

        </div>

        <p class="text-center text-sm text-gray-500 mt-6">
            💌 Dashboard atualizado automaticamente a cada revelação.
        </p>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

       {{-- 📊 GRÁFICO - PROGRESSO DAS REVELAÇÕES --}}
        <div class="mt-10 bg-white shadow-lg rounded-lg p-6 border border-gray-200">
            <h2 class="text-2xl font-bold text-indigo-700 mb-4 text-center">📊 Progresso das Revelações</h2>

            <div class="w-full flex justify-center">
                <canvas id="chartReveals" style="max-width: 240px; max-height: 240px;"></canvas>
            </div>
        </div>

        {{-- 🎁 GRÁFICO - STATUS DOS PRESENTES --}}
        <div class="mt-10 bg-white shadow-lg rounded-lg p-6 border border-gray-200">
            <h2 class="text-2xl font-bold text-indigo-700 mb-4 text-center">🎁 Status dos Presentes</h2>

            <div class="w-full flex justify-center">
                <canvas id="chartGifts" style="max-width: 240px; max-height: 240px;"></canvas>
            </div>
        </div>

    <script>
        const ctx = document.getElementById('chartReveals');

        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Já Revelaram', 'Não Revelaram'],
                datasets: [{
                    label: 'Progresso',
                    data: [{{ $revealed }}, {{ $notRevealed }}],
                    backgroundColor: [
                        '#36A2EB', // azul claro
                        '#FF6384' // rosa avermelhado
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { size: 14 },
                            color: '#333'
                        }
                    }
                }
            }
        });

        const ctx2 = document.getElementById('chartGifts');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Postaram o presente que querem', 'Já compraram o presente'],
                datasets: [{
                    label: 'Status dos Presentes',
                    data: [{{ $giftIdeaPosted}}, {{ $giftBought }}],
                    backgroundColor: ['#C084FC', '#FB923C'],
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { size: 14 }, color: '#333' }
                    }
                }
            }
        });

    </script>

    <script>
        function animateCount(el) {
            // captura o valor final (ex: 50%)
            const finalValue = parseInt(el.dataset.value);

            let start = 0;
            const duration = 1200; // ms
            const step = Math.max(1, finalValue / 100);

            // inicia o contador
            const counter = setInterval(() => {
                start += step;
                if (start >= finalValue) {
                    el.innerText = el.dataset.percent === "true" ? finalValue + '%' : finalValue;
                    clearInterval(counter);
                } else {
                    el.innerText = el.dataset.percent === "true"
                        ? Math.floor(start) + '%'
                        : Math.floor(start);
                }
            }, duration / finalValue);
        }

        window.onload = function () {
            document.querySelectorAll('.animate-stat').forEach((el, index) => {
                // define delay de animação
                el.style.animationDelay = (index * 0.15) + 's';

                // guarda valor real e identifica se é porcentagem
                const value = el.innerText;
                //el.dataset.value = parseInt(value.replace(/\D/g, ''));
                el.dataset.value = parseFloat(value.replace(',', '.'));

                el.dataset.percent = value.includes('%') ? "true" : "false";

                // 🔥 ZERA antes de aparecer!
                el.innerText = el.dataset.percent === "true" ? '0%' : '0';

                // inicia a contagem após pequena espera
                setTimeout(() => animateCount(el), (index * 150) + 300);
            });
        }
    </script>


@endsection
