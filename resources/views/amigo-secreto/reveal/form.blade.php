@extends('layouts.amigo-secreto')

@section('content')
<div class="max-w-md mx-auto px-4 mt-10">

    <div class="bg-white shadow-lg rounded-lg p-6 text-center">

        <h2 class="text-2xl md:text-3xl font-bold mb-4 text-indigo-700">
            🔐 Proteja sua revelação
        </h2>

        <p class="text-gray-600 mb-6">
            Digite sua palavra-chave pessoal e crie uma senha para revelar quem você tirou!
        </p>

        {{-- Mensagem de erro --}}
        @if($errors->any())
            <div class="bg-red-100 text-red-700 px-3 py-2 rounded mb-4 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('reveal.store', $participant) }}">
            @csrf

            {{-- Palavra-chave --}}
            <input type="text"
                   name="secret_key"
                   placeholder="Digite sua palavra-chave"
                   value="{{ $autofillKey ?? '' }}"  {{-- 👈 Preenchendo automático --}}
                   class="border rounded-lg px-3 py-2 w-full mb-4 focus:ring focus:ring-indigo-300"
                   required>


            {{-- Senha --}}
            <input type="password"
                   name="password"
                   placeholder="Crie uma senha segura"
                   class="border rounded-lg px-3 py-2 w-full mb-4 focus:ring focus:ring-indigo-300"
                   required>

            {{-- Botão --}}
            <button type="submit"
                class="bg-indigo-600 text-white px-5 py-2 rounded-lg hover:bg-indigo-700 transition w-full md:w-auto">
                Revelar 🎁
            </button>
        </form>

    </div>

</div>
@endsection
