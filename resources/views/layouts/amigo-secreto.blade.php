<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">

    <!-- VIEWPORT ESPECIAL PARA IPHONE (OBRIGATÓRIO) -->
    <meta name="viewport"
          content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    <!-- Impede zoom automático em inputs no iPhone -->
    <meta name="format-detection" content="telephone=no">

    <title>@yield('title', 'Amigo Secreto')</title>

    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="theme-color" content="#4f46e5">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/pwa-icon-180.png') }}">
    @if (request()->routeIs('home', 'dashboard.select'))
        <script src="{{ asset('pwa-install.js') }}" defer></script>
    @endif

    {{-- Tailwind via CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- CSS próprio (sempre atualizado no mobile) --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ time() }}">
</head>

<body class="bg-gray-100 text-gray-900 min-h-screen flex flex-col">

    {{-- NAVBAR SUPERIOR --}}
    <nav class="bg-indigo-600 text-white shadow relative"> {{-- ✅ (AQUI) adicionei relative no nav para reforçar o contexto do absolute --}}

        {{-- ✅ (AQUI) você tinha comentado esse container. Sem ele o menu fica “solto” e pode vazar --}}
        <div class="max-w-7xl mx-auto px-4 py-3 flex flex-wrap justify-between items-center gap-3">

            {{-- LOGO (opcional) --}}
            <a href="{{ route('home') }}"
            class="text-lg font-bold hover:text-yellow-300 flex items-center gap-2">
                <img src="{{ asset('images/pwa-icon-180.png') }}"
                    class="w-7 h-7 rounded" alt="">
                Amigo Secreto
            </a>

            {{-- MENU --}}
            <div class="flex flex-wrap items-center gap-4 text-sm font-semibold">

                @can('access-organizer')
                {{-- Trocar grupo --}}
                <div class="relative inline-block text-left">
                    <details class="group">
                        <summary
                            class="list-none cursor-pointer flex items-center gap-2 px-4 py-2 rounded-lg
                                bg-indigo-600 text-white hover:bg-indigo-700 transition">
                            {{-- ✅ (AQUI) list-none remove o marcador padrão do summary que às vezes bagunça no mobile --}}

                            <span class="font-semibold truncate max-w-[160px]">
                                {{ $group->name ?? 'Selecionar grupo' }}
                            </span>

                            <svg class="w-4 h-4 transition group-open:rotate-180"
                                fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 9l-7 7-7-7"/>
                            </svg>
                        </summary>

                        <div
                            class="absolute mt-2 z-50 overflow-hidden
                                w-64 max-w-[92vw]
                                bg-white rounded-lg shadow-lg border

                                right-0
                                sm:right-0 sm:left-auto
                                left-0 sm:left-auto">
                            {{-- ✅ (AQUI) a “trava”: no mobile usa left-0 (não vaza), em telas >= sm usa right-0 --}}

                            <ul class="max-h-64 overflow-y-auto">
                                @foreach($allGroups as $g)
                                    <li>
                                        <a href="{{ route('amigo.dashboard', $g->id) }}"
                                        title="{{ $g->name }}"
                                        class="block px-4 py-2 text-sm truncate
                                        {{ isset($group) && $group->id === $g->id
                                                ? 'bg-indigo-100 font-semibold text-indigo-700'
                                                : 'hover:bg-gray-100 font-semibold text-indigo-700' }}">
                                            {{ $g->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="border-t p-2">
                                <a href="{{ route('groups.create', ['year' => $selectedYear]) }}"
                                class="block text-sm text-indigo-600 hover:underline">
                                    ➕ Criar novo grupo
                                </a>
                            </div>
                        </div>
                    </details>
                </div>

                <a href="{{ route('dashboard.home', ['year' => $selectedYear]) }}" class="hover:underline">📊 Dashboard</a>

                <a href="{{ route('groups.index', ['year' => $selectedYear]) }}"
                class="hover:text-yellow-300 {{ request()->routeIs('groups.index') ? 'underline text-yellow-300' : '' }}">
                    👥 Grupos
                </a>

                @can('access-super-admin')
                    <a href="{{ route('super-admin.groups', absolute: false) }}"
                       class="hover:text-yellow-300 {{ request()->routeIs('super-admin.groups') ? 'underline text-yellow-300' : '' }}">
                        Painel Super Admin
                    </a>
                @endcan

                <form method="POST" action="{{ route('logout', absolute: false) }}">
                    @csrf
                    <button class="hover:underline">Sair</button>
                </form>
                @else
                    <a href="{{ route('pwa.participant', absolute: false) }}" class="hover:underline">Meu Amigo Secreto</a>
                    @unless(request()->routeIs('login'))
                        <a href="{{ route('login', absolute: false) }}" class="hover:underline">Entrar como organizador</a>
                    @endunless
                @endcan
            </div>
        </div>
    </nav>


    {{-- CONTEÚDO PRINCIPAL --}}
    <main class="flex-1 py-8">
        <div class="max-w-7xl mx-auto px-4">
            @if (request()->routeIs('dashboard.select'))
                <div class="mb-4 text-right">
                    <a href="{{ route('pwa.participant') }}" class="font-semibold text-indigo-700 underline">Meu Amigo Secreto</a>
                </div>
            @endif
            @if (request()->routeIs('dashboard.select', 'groups.index', 'amigo.dashboard'))
                <form method="GET"
                      action="{{ route(request()->routeIs('groups.index') ? 'groups.index' : 'dashboard.select') }}"
                      class="mb-6 flex flex-wrap items-center justify-end gap-2">
                    <label for="group-year" class="font-semibold">Ano</label>
                    <select id="group-year" name="year" class="rounded border border-gray-300 bg-white px-3 py-2">
                        @foreach ($availableYears as $year)
                            <option value="{{ $year }}" @selected($year === $selectedYear)>{{ $year }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded bg-indigo-600 px-3 py-2 text-white hover:bg-indigo-700">
                        Ver grupos
                    </button>
                </form>
            @endif

            @yield('content')

        </div>
    </main>

    @if (request()->routeIs('draw.show', 'dashboard.revealed-friends') && session('completed_draw_group_id') === $group->id)
        @include('amigo-secreto.reveal.sorteio-concluido')
    @endif


    {{-- FOOTER --}}
   <footer class="bg-gray-200 border-t py-4 text-center text-sm text-gray-600">
        <div>
            © 2025 Amigo Secreto — desenvolvido por 
            <b>Adriano Felix de Freitas</b>
        </div>

        <div class="mt-1 text-xs text-gray-700">
            👁️ Visitantes únicos nas últimas 24h: <strong>{{ $totalVisitors }}</strong>
        </div>
    </footer>

    {{-- Script JS opcional --}}
    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/service-worker.js').catch((error) => {
                console.warn('Não foi possível ativar o modo offline.', error);
            });
        }
    </script>
</body>
</html>
