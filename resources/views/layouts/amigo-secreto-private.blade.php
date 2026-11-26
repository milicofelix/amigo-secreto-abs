<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#4f46e5">
    <title>@yield('title', 'Amigo Secreto')</title>
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/pwa-icon-180.png') }}">
    <link rel="stylesheet" href="{{ asset('css/amigo-secreto-private.css') }}">
    @stack('head')
</head>
<body>
    <header><a href="{{ route('home', absolute: false) }}">Amigo Secreto ABS</a></header>
    <main>
        @yield('content')
    </main>
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register(@json(asset('service-worker.js'))).catch(() => {});
        }
    </script>
</body>
</html>
