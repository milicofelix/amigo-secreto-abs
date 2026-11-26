@extends('layouts.amigo-secreto')

@section('content')
 {{-- Canvas global para animação --}}
    <canvas id="confettiCanvas"
            class="fixed inset-0 w-full h-full pointer-events-none z-[9999]"></canvas>
<div class="max-w-lg mx-auto px-4 mt-10 relative">

    <div class="bg-white shadow-lg rounded-lg p-6 md:p-8 text-center relative z-10">

        {{-- Título principal --}}
        <h1 class="text-2xl md:text-3xl font-bold text-green-600 mb-4 animate-bounce">
            🎉 Parabéns, {{ $participant->name }}!
        </h1>

        @if ($assignment?->receiver)
        {{-- Subtítulo --}}
        <p class="text-lg text-gray-700 mb-4 fade-in">
            Você tirou...
        </p>

        {{-- Nome do amigo tirado --}}
        <div class="bg-green-100 text-green-800 text-2xl font-bold py-4 rounded-lg mb-6 shadow-sm reveal-zoom">
            {{ $assignment->receiver->name }}
        </div>

        {{-- Wishlist --}}
        @if($assignment->receiver->wishlist)
            <p class="text-gray-600 mb-6 fade-in">
                🎁 Sugestão: {{ $assignment->receiver->wishlist }}
            </p>
        @endif
        @else
            <p class="text-gray-700">O sorteio ainda não foi realizado.</p>
        @endif

        {{-- Link voltar --}}
        <a href="{{ route('groups.index', ['year' => $participant->group->eventYear()], absolute: false) }}"
           class="inline-block text-indigo-600 hover:text-indigo-700 font-semibold mt-2 text-base md:text-lg fade-in">
            ← Voltar aos grupos
        </a>

    </div>
</div>

{{-- ANIMAÇÃO DE CONFETES + EFEITOS --}}
<script>
document.addEventListener("DOMContentLoaded", () => {

    // Confetti Canvas
    const canvas = document.getElementById("confettiCanvas");
    const ctx = canvas.getContext("2d");

    const resize = () => {
        canvas.width = canvas.offsetWidth;
        canvas.height = canvas.offsetHeight;
    };

    resize();
    window.addEventListener("resize", resize);

    // Criar partículas
    const confetti = [];
    const colors = ["#f54291", "#42f554", "#4287f5", "#f5e642", "#f54242"];

    for (let i = 0; i < 150; i++) {
        confetti.push({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height - canvas.height,
            r: Math.random() * 6 + 4,
            c: colors[Math.floor(Math.random() * colors.length)],
            s: Math.random() * 3 + 2
        });
    }

    // Animação
    const draw = () => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        confetti.forEach((p) => {
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.r, 0, 2 * Math.PI);
            ctx.fillStyle = p.c;
            ctx.fill();

            // Movimento
            p.y += p.s;
            p.x += Math.sin(p.y * 0.02);

            // Reset ao sair da tela
            if (p.y > canvas.height) {
                p.y = -10;
                p.x = Math.random() * canvas.width;
            }
        });

        requestAnimationFrame(draw);
    };

    draw();
});
</script>

{{-- ANIMAÇÕES EXTRAS --}}
<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}

.fade-in {
    animation: fadeIn 0.9s ease forwards;
}

@keyframes zoomReveal {
    from {
        opacity: 0;
        transform: scale(0.7);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.reveal-zoom {
    animation: zoomReveal 0.6s ease-out forwards;
}
</style>
@endsection
