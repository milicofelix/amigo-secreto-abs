@extends('layouts.adm-rifa')

@section('content')
<style>
    #roleta {
        font-size: 42px;
        font-weight: bold;
        padding: 30px;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        width: 100%;
        max-width: 450px;
        margin: 50px auto;
        text-align: center;
    }
</style>

    <h1 class="text-center mt-4">🎡 Sorteio da Rifa</h1>
    <h3 class="text-center text-muted">{{ $rifa->titulo }}</h3>

    <div id="roleta">Preparando...</div>

    <script>
        const nomes = @json($nomes); 
    const escolhido = "{{ $sorteado->nome }}";

    let display = document.getElementById('roleta');

    let index = 0;
    let intervalo = 50;
    let ciclos = 0;

    function girar() {
        display.innerHTML = nomes[index].nome; 
        index = (index + 1) % nomes.length;

        ciclos++;

        if (ciclos > nomes.length * 2) intervalo = 80;
        if (ciclos > nomes.length * 4) intervalo = 120;
        if (ciclos > nomes.length * 6) intervalo = 180;

        if (intervalo > 150 && display.innerHTML === escolhido) {
            setTimeout(() => {
                window.location.href = "{{ route('admin.rifas.resultado', [$rifa->id, 'ganhador' => $sorteado->id]) }}";
            }, 1200);
            return;
        }

        setTimeout(girar, intervalo);
    }

    setTimeout(girar, 300);
</script>
@endsection
