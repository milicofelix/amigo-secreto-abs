@extends('layouts.amigo-secreto')

@section('content')
<div class="max-w-md mx-auto px-4 mt-10">

    <div class="bg-white shadow rounded-lg p-6 text-center">

        <h2 class="text-xl md:text-2xl font-bold mb-4 text-indigo-700">
            🔐 Digite sua senha
        </h2>

        <p class="text-gray-600 mb-6">
            Já existe uma senha cadastrada para este participante.<br>
            Digite para revelar o amigo secreto.
        </p>

        {{-- Mensagem de erro --}}
        @if($errors->any())
            <div class="bg-red-100 text-red-700 px-3 py-2 rounded mb-4 text-sm">
                {{ $errors->first('password') }}
            </div>
        @endif

        <form method="POST" action="{{ route('reveal.verify', $participant) }}">
            @csrf

            {{-- Campo de senha --}}
            <input type="password"
                   name="password"
                   class="border rounded-lg w-full px-3 py-2 mb-4 focus:ring focus:ring-indigo-300"
                   placeholder="Sua senha"
                   required>

            {{-- Botão --}}
            <button
                class="bg-indigo-600 text-white px-5 py-2 rounded-lg hover:bg-indigo-700 transition w-full md:w-auto font-semibold">
                Revelar 🎁
            </button>
        </form>

    </div>

</div>
@endsection
