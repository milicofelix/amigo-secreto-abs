<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Jogo do Bicho</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/jogo_bicho.css') }} ">
</head>
<body class="bg-gray-100">
    <nav class="bg-green-700 text-white p-4">
        <div class="max-w-6xl mx-auto flex justify-between">
            <a href="{{ route('bets.index') }}" class="font-bold text-lg">🐘 Jogo do Bicho</a>
            <div class="space-x-4">
                <a href="{{ route('animals.index') }}">Animais</a>
                <a href="{{ route('bets.index') }}">Apostas</a>
                <a href="{{ route('draws.index') }}">Sorteios</a>
                @auth
                <span class="saudacao">Olá, {{ Auth::user()->name }}</span>

                <a href="{{ route('dashboard') }}" class="btn-conta btn-secundario">Minha conta</a>

                <a href="{{ route('logout') }}"
                class="btn-conta btn-sair"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Sair
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                    @csrf
                </form>
        @endauth
            </div>
        </div>
    </nav>
    <main class="p-6">
        @yield('content')
    </main>
</body>
</html>
