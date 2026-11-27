@extends('layouts.amigo-secreto')

@section('content')
<section class="mx-auto max-w-lg rounded-xl bg-white p-6 shadow">
    <h1 class="mb-5 text-2xl font-bold text-indigo-700">Amigo Secreto ABS</h1>
    <div class="mb-5 flex flex-wrap gap-3">
        @if (request()->attributes->has('pwa.participant'))
            <a href="{{ route('amigo.dashboard', request()->attributes->get('pwa.participant')->group_id, absolute: false) }}"
               class="rounded bg-indigo-600 px-4 py-2 font-semibold text-white">Abrir meu grupo</a>
        @else
            <a href="{{ route('pwa.participant', absolute: false) }}" class="rounded bg-indigo-600 px-4 py-2 font-semibold text-white">
                Entrar com meu convite
            </a>
            <a href="{{ route('login', absolute: false) }}" class="rounded border border-indigo-600 px-4 py-2 font-semibold text-indigo-700">
                Entrar como organizador
            </a>
        @endif
    </div>
    <x-pwa-install />
    <x-pwa-pending-sync :participant="request()->attributes->get('pwa.participant')" />
</section>
@endsection
