@extends('layouts.amigo-secreto')

@section('content')
<div class="max-w-md mx-auto mt-10 p-6 bg-white rounded-xl shadow-lg text-center border border-gray-200">

    <h1 class="text-2xl font-bold text-indigo-700 mb-4">
        🔐 Acesso exclusivo
    </h1>

    <p class="text-gray-600 mb-6">
        Escaneie este QR Code para <strong>revelar ou rever</strong> seu amigo secreto.
    </p>

    <div class="bg-white border p-4 rounded-xl shadow mx-auto w-fit">
        <img src="data:image/png;base64,{{ $qrBase64 }}" class="w-60 mx-auto">
    </div>

    <p class="mt-6 text-lg font-bold text-indigo-800">
        {{ $participant->name }}
    </p>

    <p class="text-sm text-gray-500 mt-2">
        Não compartilhe com outras pessoas! 🔒
    </p>

    <a href="{{ url()->previous() }}"
       class="block mt-6 text-indigo-600 hover:text-indigo-800 font-semibold">
        ← Voltar
    </a>
</div>
@endsection
