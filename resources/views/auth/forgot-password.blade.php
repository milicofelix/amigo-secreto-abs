@extends('layouts.amigo-secreto')

@section('title', 'Recuperar senha')

@section('content')
<section class="mx-auto max-w-md rounded-xl bg-white p-6 shadow">
    <h1 class="mb-3 text-2xl font-bold text-indigo-700">Recuperar senha</h1>
    <div class="mb-4 text-sm text-gray-600">
        Informe o e-mail da sua conta de organizador para receber o link de recuperação.
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                Enviar link de recuperação
            </x-primary-button>
        </div>
    </form>
    <a href="{{ route('login', absolute: false) }}" class="mt-4 inline-block text-sm text-indigo-700 underline">Voltar ao login</a>
</section>
@endsection
