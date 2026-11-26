@extends('layouts.adm-rifa')

@section('content')

<style>
    .roleta-container {
        width: 100%;
        overflow: hidden;
        border: 3px solid #333;
        background: #fff;
        border-radius: 10px;
        margin-top: 40px;
    }

    .roleta {
        display: flex;
        padding: 20px;
        gap: 20px;
        font-size: 22px;
        font-weight: bold;
        white-space: nowrap;
        transition: transform 5s cubic-bezier(.15,.53,.32,1);
    }

    .item {
        padding: 15px 25px;
        background: #e9f0ff;
        border-radius: 8px;
        border: 2px solid #b0c4ff;
    }

    .pointer {
        width: 0;
        height: 0;
        border-left: 20px solid transparent;
        border-right: 20px solid transparent;
        border-bottom: 30px solid red;
        margin: auto;
        margin-top: 20px;
    }
</style>

<div class="text-center">
    <h1 class="mt-3">🎡 Sorteio da Rifa</h1>
    <h3>{{ $rifa->titulo }}</h3>
</div>

<div class="pointer"></div>

<div class="roleta-container">
    <div id="roleta" class="roleta">
        @foreach($pagos as $p)
            <div class="item" data-id="{{ $p->id }}">{{ $p->nome }}</div>
        @endforeach
    </div>
</div>

<div class="text-center mt-4">
    <button id="btnSortear" class="btn btn-danger btn-lg">🎲 Iniciar Sorteio</button>
</div>

{{-- Confetes --}}
<canvas id="confetti-canvas" style="position:fixed; top:0; left:0; width:100%; height:100%; pointer-events:none; display:none;"></canvas>

<script>
let sorteado = null;

document.getElementById('btnSortear').onclick = function () {

    const roleta = document.getElementById('roleta');
    const itens = document.querySelectorAll('.item');

    // Escolher vencedor aleatório
    const vencedor = itens[Math.floor(Math.random() * itens.length)];
    sorteado = vencedor.dataset.id;

    // Posição para alinhar o vencedor na seta (centro)
    const pos = vencedor.offsetLeft - (window.innerWidth / 2) + (vencedor.offsetWidth / 2);

    roleta.style.transform = `translateX(-${pos}px)`;

    // Ativar confetes após animação
    setTimeout(() => {
        iniciarConfetes();
        salvarGanhador(sorteado);
    }, 5200);
}

function salvarGanhador(id) {
    fetch("{{ route('admin.rifas.salvarGanhador', $rifa->id) }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ id })
    });
}

// Confetes simples
function iniciarConfetes() {
    const canvas = document.getElementById('confetti-canvas');
    canvas.style.display = "block";

    const ctx = canvas.getContext('2d');
    const confetes = [];

    for(let i = 0; i < 150; i++) {
        confetes.push({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height - canvas.height,
            r: Math.random() * 6 + 4,
            d: Math.random() * 2 + 1
        });
    }

    function desenhar() {
        ctx.clearRect(0,0,canvas.width,canvas.height);
        confetes.forEach(c => {
            ctx.beginPath();
            ctx.arc(c.x, c.y, c.r, 0, Math.PI * 2);
            ctx.fillStyle = '#'+Math.floor(Math.random()*16777215).toString(16);
            ctx.fill();

            c.y += c.d;
            if(c.y > canvas.height) c.y = -10;
        });

        requestAnimationFrame(desenhar);
    }

    desenhar();
}
</script>

@endsection
