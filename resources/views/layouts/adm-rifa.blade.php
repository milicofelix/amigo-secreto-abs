<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Sistema de Rifas') }}</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f5f7f9;
        }

        .navbar-custom {
            background: #1f2937; /* cinza escuro elegante */
        }

        .navbar-custom .navbar-brand,
        .navbar-custom .nav-link {
            color: #fff !important;
            font-weight: 500;
        }

        .card-hover:hover {
            background: #eef2ff; /* leve tom azul */
            transition: 0.2s;
        }
    </style>
</head>

<body>

    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-custom mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                🎟️ Sistema de Rifas
            </a>

            <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">

                <ul class="navbar-nav ms-auto">

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('rifas.index') }}">Rifas</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#">Cadastrar Rifa</a>
                    </li>

                    @auth
                        <li class="nav-item">
                            <span class="nav-link">Olá, {{ auth()->user()->name }}</span>
                        </li>

                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="btn btn-sm btn-danger ms-2">Sair</button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="btn btn-sm btn-outline-light ms-2" href="#">Entrar</a>
                        </li>
                    @endauth
                </ul>

            </div>
        </div>
    </nav>

    {{-- Área principal --}}
    <div class="container mb-5">

        {{-- Mensagens Flash --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if(session('winner'))
            <div class="alert alert-success">
                🎉 <strong>Ganhador:</strong> {{ session('winner')->nome }} <br>
                Comprado por: {{ session('winner')->comprador->name ?? 'Desconhecido' }}
            </div>
        @endif

        {{-- Conteúdo da página --}}
        @yield('content')

    </div>

    {{-- Footer --}}
    <footer class="text-center py-3 mt-4" style="background:#1f2937; color:#fff;">
        <p class="mb-0">Sistema de Rifas • Desenvolvido por {{ auth()->user()->name ?? 'NextFelix Tech' }}</p>
    </footer>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
