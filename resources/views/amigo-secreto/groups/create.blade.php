@extends('layouts.amigo-secreto')

@section('content')
<div class="max-w-3xl mx-auto mt-8 px-4">

    <h1 class="text-2xl md:text-3xl font-bold mb-6 text-black">
        🎉 Criar Novo Grupo
    </h1>

    <form method="POST" action="{{ route('groups.store') }}"
          class="bg-white shadow rounded p-5">

        @csrf

        {{-- Nome --}}
        <div class="mb-4">
            <label class="block font-semibold mb-1">Nome do Grupo</label>
            <input type="text" name="name"
                   class="w-full border rounded p-2"
                   required>
        </div>

        {{-- Descrição --}}
        <div class="mb-4">
            <label class="block font-semibold mb-1">Descrição</label>
            <textarea name="description"
                      class="w-full border rounded p-2"
                      rows="3"></textarea>
        </div>

        {{-- Valores mínimo/máximo --}}
        <div class="flex flex-col md:flex-row gap-4 mb-4">
            <div class="flex-1">
                <label class="block font-semibold mb-1">Valor Mínimo</label>
                <input type="number" step="0.01" name="budget_min"
                       class="w-full border rounded p-2">
            </div>

            <div class="flex-1">
                <label class="block font-semibold mb-1">Valor Máximo</label>
                <input type="number" step="0.01" name="budget_max"
                       class="w-full border rounded p-2">
            </div>
        </div>

        {{-- Data --}}
        <div class="mb-4">
            <label class="block font-semibold mb-1">Data do Evento</label>
            <input type="date" name="event_date"
                   class="w-full border rounded p-2">
        </div>

        {{-- Local --}}
        <div class="mb-6">
            <label class="block font-semibold mb-1">Local do Evento</label>
            <input type="text" name="event_location"
                   class="w-full border rounded p-2">
        </div>

        {{-- Botão --}}
        <button class="bg-green-600 text-white px-5 py-2 rounded w-full md:w-auto">
            Salvar
        </button>

    </form>

</div>
@endsection
