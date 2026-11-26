@extends('layouts.amigo-secreto')

@section('content')
<div class="max-w-md mx-auto mt-10">

    <h1 class="text-2xl font-bold text-indigo-700 mb-6 text-center">
        📊 Escolha um grupo
    </h1>

    <x-pwa-install />
    <x-pwa-pending-sync :participant="request()->attributes->get('pwa.participant')" />

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
