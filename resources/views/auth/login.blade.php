@extends('layouts.amigo-secreto')

@section('title', 'Entrar como organizador')

@section('content')
<section class="mx-auto max-w-md rounded-xl bg-white p-6 shadow">
    <h1 class="mb-4 text-2xl font-bold text-indigo-700">Entrar como organizador</h1>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login', absolute: false) }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="block font-semibold">E-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   autocomplete="username" class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
        <div>
            <label for="password" class="block font-semibold">Senha</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <button class="rounded bg-indigo-600 px-4 py-2 font-semibold text-white">Entrar</button>
            <a href="{{ route('password.request', absolute: false) }}" class="text-sm text-indigo-700 underline">Esqueci minha senha</a>
        </div>
    </form>
</section>
@endsection
