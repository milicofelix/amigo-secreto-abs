<dialog id="sorteio-concluido" aria-labelledby="sorteio-concluido-titulo"
        class="m-auto w-[calc(100%-2rem)] max-w-md rounded-xl bg-white p-6 text-center shadow-xl backdrop:bg-black/50">
    <div aria-hidden="true" class="mb-3 text-4xl">🎉</div>
    <h2 id="sorteio-concluido-titulo" class="text-2xl font-bold text-indigo-700">Sorteio realizado!</h2>
    <p class="mt-2 text-gray-700">Agora você pode descobrir quem tirou!</p>
    <form method="dialog" class="mt-5">
        <button autofocus class="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
            Continuar
        </button>
    </form>
</dialog>

<script>
    document.getElementById('sorteio-concluido').showModal();
</script>
