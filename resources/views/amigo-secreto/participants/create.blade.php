@extends('layouts.amigo-secreto')

@section('page-title', 'Novo Participante')

@section('content')
<div class="max-w-3xl mx-auto px-4 mt-8">

    <h1 class="text-2xl md:text-3xl font-bold mb-6 text-white">
        ➕ Novo Participante — {{ $group->name }}
    </h1>

    <form method="POST" action="{{ route('participants.store', $group) }}" 
          enctype="multipart/form-data"
          class="bg-white shadow rounded p-5">
        @csrf
        @include('amigo-secreto.participants._form')

        <div class="mt-6">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded w-full md:w-auto">
                Adicionar
            </button>
        </div>
    </form>

    <a href="{{ route('groups.edit', $group, absolute: false) }}" class="mt-4 inline-block font-semibold text-indigo-700 underline">
        ← Voltar ao grupo
    </a>
</div>
@endsection
