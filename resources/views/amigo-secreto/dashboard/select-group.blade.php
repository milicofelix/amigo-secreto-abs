@extends('layouts.amigo-secreto')

@section('content')
<div class="max-w-md mx-auto mt-10">

    <h1 class="text-2xl font-bold text-indigo-700 mb-6 text-center">
        📊 Escolha um grupo
    </h1>

    <aside id="pwa-install" class="mb-5 rounded-lg border border-indigo-100 bg-white p-4" aria-label="Instalação do app" hidden>
        <button type="button" id="pwa-install-button"
                class="w-full rounded bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700" hidden>
            Instalar app
        </button>

        <details id="pwa-install-iphone" hidden>
            <summary class="cursor-pointer font-semibold text-indigo-700">Instalar no iPhone ou iPad</summary>
            <ol class="mt-3 list-decimal space-y-1 pl-5 text-sm text-gray-700">
                <li>Abra esta página no Safari e toque em <strong>Compartilhar</strong>.</li>
                <li>Escolha <strong>Adicionar à Tela de Início</strong>.</li>
                <li>Ative <strong>Abrir como App da Web</strong>, se a opção aparecer. Toque em <strong>Adicionar</strong>.</li>
            </ol>
        </details>
    </aside>

    <div class="space-y-4">
        @forelse ($groups as $group)
            <a href="{{ route('amigo.dashboard', $group->id) }}"
               class="block bg-white shadow-md rounded-lg p-4 border-l-4 border-indigo-500 hover:bg-indigo-50 transition">
                
                <div class="font-semibold text-lg text-indigo-700">
                    {{ $group->name }}
                </div>

                <div class="text-sm text-gray-600">
                    👥 {{ $group->participants_count }} participantes
                </div>
            </a>
        @empty
            <div class="rounded-lg bg-white p-4 text-center text-gray-700">
                <p>Nenhum grupo em {{ $selectedYear }}.</p>
                <a href="{{ route('groups.create', ['year' => $selectedYear]) }}"
                   class="mt-3 inline-block font-semibold text-indigo-700 hover:underline">Criar grupo</a>
            </div>
        @endforelse
    </div>

</div>
@endsection
