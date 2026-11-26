@extends('layouts.amigo-secreto')

@section('content')
<style>
    #roleta {
        font-size: 32px;
        font-weight: bold;
        padding: 25px;
        background: #ffffff;
        border-radius: 14px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        width: 100%;
        max-width: 500px;
        margin: 40px auto;
        text-align: center;
        border-left: 6px solid #6366F1; /* lilás do dashboard */
    }
</style>

<h1 class="text-center text-3xl font-bold mt-6 text-indigo-700">
    🎉 Sorteio — Quem começa a revelar?
</h1>

<h3 class="text-center text-gray-600 mb-6">{{ $group->name }}</h3>

<div id="roleta">Carregando...</div>

<script>
    const nomes = @json($nomes);
    const escolhido = "{{ $sorteado->name }}";

    let display = document.getElementById('roleta');
    let index = 0;
    let intervalo = 60;
    let ciclos = 0;

    function girar() {
        display.innerHTML = nomes[index].nome;
        index = (index + 1) % nomes.length;
        ciclos++;

        if (ciclos > nomes.length * 2) intervalo = 80;
        if (ciclos > nomes.length * 4) intervalo = 120;
        if (ciclos > nomes.length * 6) intervalo = 180;

        // hora de parar
        if (intervalo > 150 && display.innerHTML === escolhido) {
            setTimeout(() => {
                display.innerHTML = "🎉 " + escolhido + " começa!";
                display.style.background = "#EEF2FF";
                display.style.borderLeft = "6px solid #4F46E5";
            }, 800);
            return;
        }

        setTimeout(girar, intervalo);
    }

    setTimeout(girar, 300);
</script>

<div class="text-center mt-6">
    <a href="{{ route('amigo.dashboard', $group, absolute: false) }}"
        class="bg-indigo-600 text-white px-5 py-2 rounded-lg hover:bg-indigo-700">
        ⬅️ Voltar ao dashboard
    </a>
</div>

@endsection
