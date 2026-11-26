<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎯 Portal das Loterias</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(145deg, #fffbe5 0%, #f6ffe7 40%, #dfffd7 100%);
            background-image: url('{{ asset('images/bg-loteria.png') }}');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(5px);
            border-radius: 20px;
            padding: 3rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            text-align: center;
            border: 2px solid #ffe066;
        }

        .login-container img.logo {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            border: 4px solid #f1c40f;
            margin-bottom: 10px;
            object-fit: cover;
        }

        h1 {
            color: #1b5e20;
            font-weight: 800;
            font-size: 1.9rem;
        }

        p.subtitle {
            color: #388e3c;
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        .input-group {
            text-align: left;
            margin-bottom: 1.3rem;
        }

        label {
            display: block;
            color: #2e7d32;
            font-weight: 600;
            margin-bottom: 6px;
        }

        input {
            width: 100%;
            padding: 0.7rem;
            border: 1px solid #bdbdbd;
            border-radius: 10px;
            font-size: 1rem;
            outline: none;
            transition: 0.2s;
        }

        input:focus {
            border-color: #43a047;
            box-shadow: 0 0 0 2px rgba(76,175,80,0.2);
        }

        .actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #2e7d32;
        }

        .forgot a {
            color: #f1a100;
            text-decoration: none;
            font-weight: 600;
        }

        .forgot a:hover {
            text-decoration: underline;
        }

        .btn {
            background: linear-gradient(135deg, #43a047, #2e7d32);
            color: white;
            border: none;
            width: 100%;
            padding: 0.9rem;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.2s;
            box-shadow: 0 4px 10px rgba(46,125,50,0.3);
        }

        .btn:hover {
            transform: scale(1.03);
            background: linear-gradient(135deg, #4caf50, #1b5e20);
        }

        .footer {
            margin-top: 2rem;
            font-size: 0.8rem;
            color: #33691e;
        }

        .footer span {
            color: #f9a825;
            font-weight: bold;
        }

        .floating {
            position: absolute;
            opacity: 0.3;
            animation: float 6s infinite ease-in-out;
        }

        @keyframes float {
            0%,100% { transform: translateY(0); }
            50% { transform: translateY(-25px); }
        }

        .floating img {
            width: 120px;
            height: 120px;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>

    <!-- Elementos decorativos -->
    <div class="floating" style="top:15%; left:10%; animation-delay: 0s;">
        <img src="{{ asset('images/trevo.png') }}" alt="Trevo">
    </div>
    <div class="floating" style="bottom:10%; right:12%; animation-delay: 1.5s;">
        <img src="{{ asset('images/dado.png') }}" alt="Dado">
    </div>
    <div class="floating" style="top:60%; left:5%; animation-delay: 2.5s;">
        <img src="{{ asset('images/ferradura.png') }}" alt="Ferradura">
    </div>

    <!-- Container principal -->
    <div class="login-container">
        <img class="logo" src="{{ asset('images/logo.png') }}" alt="Logo">
        <h1>Portal das Loterias</h1>
        <p class="subtitle">🎲 Entre e veja se a sorte está do seu lado!</p>

        {{-- ✅ Formulário funcional --}}
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="input-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" placeholder="seuemail@dominio.com" required autofocus autocomplete="username" value="{{ old('email') }}">
                @error('email')
                    <p style="color: red; font-size: 0.85rem;">{{ $message }}</p>
                @enderror
            </div>

            <div class="input-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                @error('password')
                    <p style="color: red; font-size: 0.85rem;">{{ $message }}</p>
                @enderror
            </div>

            <div class="actions">
                <label class="remember">
                    <input type="checkbox" name="remember"> Lembrar-me
                </label>
                @if (Route::has('password.request'))
                    <div class="forgot">
                        <a href="{{ route('password.request') }}">Esqueceu a senha?</a>
                    </div>
                @endif
            </div>

            <button type="submit" class="btn">🎯 Entrar no Sistema</button>
        </form>

        <div class="footer">
            🍀 Boa sorte nas apostas! <br>
            <span>NextFelix Technologies</span> © 2025
        </div>
    </div>

</body>
</html>
