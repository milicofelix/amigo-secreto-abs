@extends('layouts.amigo-secreto')

@section('page-title', 'Editar Participante')

@section('content')
<div class="max-w-3xl mx-auto px-4 mt-8">

    <h1 class="text-2xl md:text-3xl font-bold mb-6 text-black">
        ✏️ Editar Participante — {{ $group->name }}
    </h1>

    <form method="POST"
          action="{{ route('participants.update', [$group, $participant]) }}"
          enctype="multipart/form-data"
          class="bg-white shadow rounded p-5">

        @csrf
        @method('PUT')

        {{-- Formulário compartilhado --}}
        @include('amigo-secreto.participants._form')

        {{-- Botão de submit --}}
        <div class="mt-6 text-right">
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded w-full md:w-auto">
                💾 Atualizar
            </button>
        </div>
    </form>

</div>
@endsection
